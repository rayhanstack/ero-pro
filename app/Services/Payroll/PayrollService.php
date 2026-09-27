<?php

namespace App\Services\Payroll;

use App\Enums\AttendanceStatusEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Enums\PayslipStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Events\PayslipPaid;
use App\Models\Attendance;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryComponent;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\SalaryComponent;
use App\Models\User;
use App\Services\Hr\CalendarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PayrollService
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    /**
     * Get paginated payroll periods with filters.
     */
    public function getPaginatedPeriods(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PayrollPeriod::query()
            ->withCount('payslips')
            ->withSum('payslips as total_net_pay', 'net_pay')
            ->withSum('payslips as total_basic_pay', 'basic');

        if (! empty($filters['year'])) {
            $query->forYear((int) $filters['year']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        return $query->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get summary statistics for payroll periods.
     */
    public function getPeriodStats(): array
    {
        return [
            'total_periods' => PayrollPeriod::count(),
            'draft' => PayrollPeriod::where('status', PayrollPeriodStatusEnum::DRAFT->value)->count(),
            'processed' => PayrollPeriod::where('status', PayrollPeriodStatusEnum::PROCESSED->value)->count(),
            'paid' => PayrollPeriod::where('status', PayrollPeriodStatusEnum::PAID->value)->count(),
            'locked' => PayrollPeriod::where('status', PayrollPeriodStatusEnum::LOCKED->value)->count(),
        ];
    }

    /**
     * Create a new payroll period cycle.
     */
    public function createPeriod(array $data): PayrollPeriod
    {
        $month = (int) $data['month'];
        $year = (int) $data['year'];

        $startDate = ! empty($data['start_date'])
            ? Carbon::parse($data['start_date'])
            : Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $endDate = ! empty($data['end_date'])
            ? Carbon::parse($data['end_date'])
            : Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $name = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return PayrollPeriod::create([
            'name' => $name,
            'month' => $month,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);
    }

    /**
     * Generate payroll for all active employees for a given period.
     * Idempotent: Overwrites existing draft/processed payslips.
     */
    public function generate(PayrollPeriod $period): int
    {
        if ($period->status === PayrollPeriodStatusEnum::LOCKED) {
            throw new \RuntimeException(_trans('common.Cannot generate or modify payroll for a locked period.'));
        }

        return DB::transaction(function () use ($period) {
            $startDate = $period->start_date instanceof Carbon ? $period->start_date : Carbon::parse($period->start_date ?? "{$period->year}-{$period->month}-01")->startOfMonth();
            $endDate = $period->end_date instanceof Carbon ? $period->end_date : Carbon::parse($period->end_date ?? "{$period->year}-{$period->month}-01")->endOfMonth();

            // 1. Delete existing draft/processed payslips and their items for idempotency
            $existingPayslips = Payslip::where('payroll_period_id', $period->id)
                ->whereIn('status', [PayslipStatusEnum::DRAFT->value, PayslipStatusEnum::APPROVED->value])
                ->get();

            foreach ($existingPayslips as $oldPayslip) {
                $oldPayslip->items()->delete();
                $oldPayslip->forceDelete();
            }

            // 2. Working days in month basis from settings
            $workingDaysMode = globalSetting('working_days_mode', 'monthly_fixed');
            $workingDaysBasis = match ($workingDaysMode) {
                'calendar_days' => (float) $startDate->daysInMonth,
                'working_days' => (float) max(1, $this->calendarService->workingDaysBetween($startDate, $endDate)),
                default => 30.00, // monthly_fixed
            };

            $overtimeMultiplier = (float) globalSetting('overtime_rate', '1.5');

            // 3. Load active employees with their details and salary component mapping
            $employees = User::active()
                ->whereHas('employeeDetail')
                ->with([
                    'employeeDetail',
                    'employeeSalaryComponents.component',
                ])
                ->get();

            $generatedCount = 0;
            $activeComponents = SalaryComponent::active()->get()->keyBy('id');

            foreach ($employees as $employee) {
                $basicSalary = (float) ($employee->employeeDetail?->basic_salary ?? 0.00);
                $perDayRate = $workingDaysBasis > 0 ? round($basicSalary / $workingDaysBasis, 4) : 0.00;

                // --- Attendance Statistics ---
                $attendances = Attendance::forEmployee($employee->id)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->get();

                $presentCount = 0.0;
                $overtimeMinutes = 0;
                $explicitAbsentDays = 0.0;

                foreach ($attendances as $att) {
                    if (in_array($att->status, [AttendanceStatusEnum::PRESENT, AttendanceStatusEnum::LATE])) {
                        $presentCount += 1.0;
                    } elseif ($att->status === AttendanceStatusEnum::HALF_DAY) {
                        $presentCount += 0.5;
                        $explicitAbsentDays += 0.5;
                    } elseif ($att->status === AttendanceStatusEnum::ABSENT) {
                        $explicitAbsentDays += 1.0;
                    }

                    $overtimeMinutes += (int) $att->overtime_minutes;
                }

                // --- Leave Statistics (Approved Leaves) ---
                $leaves = LeaveRequest::approved()
                    ->where('employee_id', $employee->id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('from_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('to_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    })
                    ->with('leaveType')
                    ->get();

                $paidLeaveDays = 0.0;
                $unpaidLeaveDays = 0.0;

                foreach ($leaves as $leave) {
                    $lFrom = Carbon::parse($leave->from_date)->max($startDate);
                    $lTo = Carbon::parse($leave->to_date)->min($endDate);
                    $lDays = (float) $leave->days;

                    if ($leave->leaveType?->is_paid) {
                        $paidLeaveDays += $lDays;
                    } else {
                        $unpaidLeaveDays += $lDays;
                    }
                }

                // Determine effective present, leave, and absent days
                $totalLeaveDays = $paidLeaveDays + $unpaidLeaveDays;
                $effectivePresentDays = $presentCount > 0 ? $presentCount : max(0, $workingDaysBasis - $totalLeaveDays);
                $effectiveAbsentDays = $unpaidLeaveDays + $explicitAbsentDays;

                // Overtime calculation
                $hourlyRate = $perDayRate > 0 ? ($perDayRate / 8.0) : 0.00;
                $perMinuteRate = $hourlyRate / 60.0;
                $overtimeAmount = round($overtimeMinutes * $perMinuteRate * $overtimeMultiplier, 2);

                // Absent deduction
                $absentDeduction = round($effectiveAbsentDays * $perDayRate, 2);

                // --- Salary Components Calculation ---
                $totalEarnings = 0.00;
                $totalDeductions = 0.00;
                $payslipItemsData = [];

                $assignedMap = $employee->employeeSalaryComponents->keyBy('component_id');

                // Apply assigned components (or fall back to active components if assigned)
                foreach ($assignedMap as $compId => $assigned) {
                    $comp = $assigned->component;
                    if (! $comp || $comp->status->value !== 'active') {
                        continue;
                    }

                    $amount = $comp->calculateAmount($basicSalary, $assigned->value);
                    $valOrRate = $assigned->value !== null ? (float) $assigned->value : (float) $comp->value;

                    if ($comp->type === SalaryComponentTypeEnum::EARNING) {
                        $totalEarnings += $amount;
                    } else {
                        $totalDeductions += $amount;
                    }

                    $payslipItemsData[] = [
                        'component_id' => $comp->id,
                        'name' => $comp->name,
                        'type' => $comp->type->value,
                        'calc_type' => $comp->calc_type->value,
                        'rate_or_value' => $valOrRate,
                        'amount' => $amount,
                    ];
                }

                // Tax & Bonus defaults
                $tax = 0.00;
                $bonus = 0.00;

                // Net Pay computation
                $grossPay = $basicSalary + $totalEarnings + $overtimeAmount + $bonus;
                $deductionsSum = $totalDeductions + $absentDeduction + $tax;
                $netPay = max(0, round($grossPay - $deductionsSum, 2));

                $payslipNumber = sprintf('PS-%d%02d-%04d', $period->year, $period->month, $employee->id);

                // Create Payslip
                $payslip = Payslip::create([
                    'payslip_number' => $payslipNumber,
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                    'basic' => $basicSalary,
                    'total_earnings' => round($totalEarnings, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'overtime_amount' => $overtimeAmount,
                    'absent_deduction' => $absentDeduction,
                    'tax' => $tax,
                    'bonus' => $bonus,
                    'net_pay' => $netPay,
                    'working_days' => $workingDaysBasis,
                    'present_days' => $effectivePresentDays,
                    'leave_days' => $totalLeaveDays,
                    'absent_days' => $effectiveAbsentDays,
                    'status' => PayslipStatusEnum::DRAFT,
                ]);

                // Create Item snapshots
                foreach ($payslipItemsData as $itemData) {
                    $itemData['payslip_id'] = $payslip->id;
                    PayslipItem::create($itemData);
                }

                $generatedCount++;
            }

            // Update period status to processed
            $period->update(['status' => PayrollPeriodStatusEnum::PROCESSED]);

            return $generatedCount;
        });
    }

    /**
     * Get paginated payslips for a specific payroll period.
     */
    public function getPaginatedPayslips(PayrollPeriod $period, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payslip::query()
            ->forPeriod($period->id)
            ->with([
                'employee.employeeDetail.department',
                'employee.employeeDetail.designation',
                'items',
            ]);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee.employeeDetail', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Update individual payslip attributes (bonus, tax, overtime, absent deduction, note).
     */
    public function updatePayslip(Payslip $payslip, array $data): Payslip
    {
        return DB::transaction(function () use ($payslip, $data) {
            $bonus = isset($data['bonus']) ? (float) $data['bonus'] : (float) $payslip->bonus;
            $tax = isset($data['tax']) ? (float) $data['tax'] : (float) $payslip->tax;
            $overtime = isset($data['overtime_amount']) ? (float) $data['overtime_amount'] : (float) $payslip->overtime_amount;
            $absentDeduction = isset($data['absent_deduction']) ? (float) $data['absent_deduction'] : (float) $payslip->absent_deduction;
            $note = $data['note'] ?? $payslip->note;

            $grossPay = (float) $payslip->basic + (float) $payslip->total_earnings + $overtime + $bonus;
            $deductions = (float) $payslip->total_deductions + $absentDeduction + $tax;
            $netPay = max(0, round($grossPay - $deductions, 2));

            $payslip->update([
                'bonus' => $bonus,
                'tax' => $tax,
                'overtime_amount' => $overtime,
                'absent_deduction' => $absentDeduction,
                'net_pay' => $netPay,
                'note' => $note,
            ]);

            return $payslip->fresh(['employee.employeeDetail', 'items']);
        });
    }

    /**
     * Approve a single payslip.
     */
    public function approvePayslip(Payslip $payslip): bool
    {
        return (bool) $payslip->update(['status' => PayslipStatusEnum::APPROVED]);
    }

    /**
     * Bulk approve all payslips for a period.
     */
    public function bulkApprovePayslips(PayrollPeriod $period): int
    {
        return Payslip::where('payroll_period_id', $period->id)
            ->where('status', PayslipStatusEnum::DRAFT->value)
            ->update(['status' => PayslipStatusEnum::APPROVED->value]);
    }

    /**
     * Mark a payslip as Paid and dispatch PayslipPaid event.
     */
    public function markPaid(Payslip $payslip, ?string $paymentMethod = 'Bank Transfer'): bool
    {
        return DB::transaction(function () use ($payslip, $paymentMethod) {
            $payslip->update([
                'status' => PayslipStatusEnum::PAID,
                'paid_at' => now(),
                'payment_method' => $paymentMethod ?? 'Bank Transfer',
            ]);

            // Dispatch Event with hook for Finance expense module
            event(new PayslipPaid($payslip));

            // Check if all payslips in this period are now paid
            $period = $payslip->payrollPeriod;
            if ($period && ! Payslip::where('payroll_period_id', $period->id)->where('status', '!=', PayslipStatusEnum::PAID->value)->exists()) {
                $period->update(['status' => PayrollPeriodStatusEnum::PAID]);
            }

            return true;
        });
    }

    /**
     * Bulk mark all approved payslips in a period as Paid.
     */
    public function bulkMarkPaid(PayrollPeriod $period, ?string $paymentMethod = 'Bank Transfer'): int
    {
        return DB::transaction(function () use ($period, $paymentMethod) {
            $approvedPayslips = Payslip::where('payroll_period_id', $period->id)
                ->where('status', PayslipStatusEnum::APPROVED->value)
                ->get();

            $paidCount = 0;
            foreach ($approvedPayslips as $payslip) {
                $this->markPaid($payslip, $paymentMethod);
                $paidCount++;
            }

            if (! Payslip::where('payroll_period_id', $period->id)->where('status', '!=', PayslipStatusEnum::PAID->value)->exists()) {
                $period->update(['status' => PayrollPeriodStatusEnum::PAID]);
            }

            return $paidCount;
        });
    }

    /**
     * Lock a payroll period preventing any further alterations.
     */
    public function lockPeriod(PayrollPeriod $period): bool
    {
        return (bool) $period->update(['status' => PayrollPeriodStatusEnum::LOCKED]);
    }

    /**
     * Get paginated payslips for the authenticated employee.
     */
    public function getMyPayslips(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Payslip::query()
            ->forEmployee($user->id)
            ->whereIn('status', [PayslipStatusEnum::APPROVED->value, PayslipStatusEnum::PAID->value])
            ->with(['payrollPeriod', 'items'])
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Generate PDF stream/download for a payslip.
     */
    public function generatePdf(Payslip $payslip): Response
    {
        $payslip->loadMissing([
            'payrollPeriod',
            'employee.employeeDetail.department',
            'employee.employeeDetail.designation',
            'employee.primaryBankAccount',
            'items.component',
        ]);

        $company = [
            'name' => globalSetting('company_name', 'ERP Pro Inc.'),
            'email' => globalSetting('company_email', 'finance@erppro.com'),
            'phone' => globalSetting('company_phone', '+1 (555) 019-2834'),
            'address' => globalSetting('company_address', '100 Enterprise Blvd, Suite 400, Tech City'),
            'logo' => globalSetting('company_logo'),
        ];

        $pdf = Pdf::loadView('admin.payroll.payslips.pdf', compact('payslip', 'company'));
        $pdf->setPaper('a4', 'portrait');

        $fileName = "Payslip-{$payslip->payslip_number}.pdf";

        return $pdf->download($fileName);
    }
}
