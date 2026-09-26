<?php

namespace App\Services\Leave;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\LeaveRequestStatusEnum;
use App\Helpers\MediaHelper;
use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use App\Services\Hr\CalendarService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    /**
     * Calculate total working days in a date range, excluding weekends and holidays.
     */
    public function calculateDays(int|string $employeeId, string|DateTimeInterface $fromDate, string|DateTimeInterface $toDate, bool $halfDay = false): float
    {
        $start = $fromDate instanceof Carbon ? $fromDate->copy()->startOfDay() : Carbon::parse($fromDate)->startOfDay();
        $end = $toDate instanceof Carbon ? $toDate->copy()->startOfDay() : Carbon::parse($toDate)->startOfDay();

        if ($start->gt($end)) {
            return 0.0;
        }

        if ($halfDay) {
            // Half day applies to the start date
            if ($this->calendarService->isWeekend($start) || $this->calendarService->isHoliday($start)) {
                return 0.0;
            }

            return 0.5;
        }

        $period = CarbonPeriod::create($start, '1 day', $end);
        $workingDays = 0.0;

        foreach ($period as $date) {
            if (! $this->calendarService->isWeekend($date) && ! $this->calendarService->isHoliday($date)) {
                $workingDays += 1.0;
            }
        }

        return $workingDays;
    }

    /**
     * Check if an employee has sufficient leave balance for a given type.
     */
    public function checkBalance(int $employeeId, int $leaveTypeId, float $days, ?int $year = null): bool
    {
        $year = $year ?? (int) date('Y');

        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (! $balance) {
            // Check if leave type exists and auto-create balance for the year
            $leaveType = LeaveType::find($leaveTypeId);
            if (! $leaveType) {
                return false;
            }

            $balance = LeaveBalance::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'allocated' => $leaveType->days_per_year,
                'used' => 0,
                'carried' => 0,
            ]);
        }

        return $balance->remaining >= $days;
    }

    /**
     * Get or create balance for an employee, leave type and year.
     */
    public function getOrCreateBalance(int $employeeId, int $leaveTypeId, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'allocated' => LeaveType::find($leaveTypeId)?->days_per_year ?? 0,
                'used' => 0,
                'carried' => 0,
            ]
        );
    }

    /**
     * Submit a new leave request.
     */
    public function apply(array $data, int $employeeId): LeaveRequest
    {
        $fromDate = Carbon::parse($data['from_date']);
        $toDate = ! empty($data['half_day']) ? $fromDate->copy() : Carbon::parse($data['to_date']);
        $halfDay = (bool) ($data['half_day'] ?? false);
        $leaveTypeId = (int) $data['leave_type_id'];
        $year = (int) $fromDate->format('Y');

        $days = $this->calculateDays($employeeId, $fromDate, $toDate, $halfDay);

        if ($days <= 0) {
            throw ValidationException::withMessages([
                'from_date' => [_trans('common.The selected date range contains no working days.')],
            ]);
        }

        if (! $this->checkBalance($employeeId, $leaveTypeId, $days, $year)) {
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->first();
            $remaining = $balance ? $balance->remaining : 0;

            throw ValidationException::withMessages([
                'leave_type_id' => [_trans('common.Insufficient leave balance. Available balance: :count day(s).', ['count' => $remaining])],
            ]);
        }

        // Check overlapping leave requests
        $overlapping = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', [LeaveRequestStatusEnum::PENDING, LeaveRequestStatusEnum::APPROVED])
            ->where(function (Builder $q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                    ->orWhereBetween('to_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                    ->orWhere(function (Builder $inner) use ($fromDate, $toDate) {
                        $inner->where('from_date', '<=', $fromDate->format('Y-m-d'))
                            ->where('to_date', '>=', $toDate->format('Y-m-d'));
                    });
            })
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'from_date' => [_trans('common.You already have a pending or approved leave request for these dates.')],
            ]);
        }

        $attachmentPath = null;
        if (! empty($data['attachment']) && $data['attachment'] instanceof \Illuminate\Http\UploadedFile) {
            $attachmentPath = MediaHelper::upload($data['attachment'], 'leaves');
        }

        return LeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'days' => $days,
            'half_day' => $halfDay,
            'half_day_type' => $halfDay ? ($data['half_day_type'] ?? 'first_half') : null,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => LeaveRequestStatusEnum::PENDING,
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leaveRequest, int $approverId, ?string $remark = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approverId, $remark) {
            $year = (int) $leaveRequest->from_date->format('Y');

            // Deduct balance
            $balance = $this->getOrCreateBalance($leaveRequest->employee_id, $leaveRequest->leave_type_id, $year);
            $balance->increment('used', $leaveRequest->days);

            // Update status
            $leaveRequest->update([
                'status' => LeaveRequestStatusEnum::APPROVED,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'remark' => $remark,
            ]);

            // Mark Attendance records for each working day
            $period = CarbonPeriod::create($leaveRequest->from_date, '1 day', $leaveRequest->to_date);
            $leaveTypeName = $leaveRequest->leaveType?->name ?? 'Leave';

            foreach ($period as $date) {
                if (! $this->calendarService->isWeekend($date) && ! $this->calendarService->isHoliday($date)) {
                    $dateStr = $date->format('Y-m-d');
                    $status = $leaveRequest->half_day ? AttendanceStatusEnum::HALF_DAY : AttendanceStatusEnum::LEAVE;
                    $note = "Leave Approved: {$leaveTypeName}" . ($leaveRequest->half_day ? ' (Half Day)' : '');

                    Attendance::updateOrCreate(
                        [
                            'employee_id' => $leaveRequest->employee_id,
                            'date' => $dateStr,
                        ],
                        [
                            'status' => $status,
                            'source' => AttendanceSourceEnum::MANUAL,
                            'note' => $note,
                        ]
                    );
                }
            }

            // Send database notification
            $leaveRequest->employee?->notify(new LeaveStatusNotification($leaveRequest, 'approved'));

            return $leaveRequest->fresh(['leaveType', 'employee', 'approver']);
        });
    }

    /**
     * Reject a leave request.
     */
    public function reject(LeaveRequest $leaveRequest, int $approverId, ?string $remark = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approverId, $remark) {
            $leaveRequest->update([
                'status' => LeaveRequestStatusEnum::REJECTED,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'remark' => $remark,
            ]);

            // Send database notification
            $leaveRequest->employee?->notify(new LeaveStatusNotification($leaveRequest, 'rejected'));

            return $leaveRequest->fresh(['leaveType', 'employee', 'approver']);
        });
    }

    /**
     * Cancel a leave request.
     */
    public function cancel(LeaveRequest $leaveRequest, int $userId): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest) {
            // If already approved, rollback balance and attendance
            if ($leaveRequest->status === LeaveRequestStatusEnum::APPROVED) {
                $year = (int) $leaveRequest->from_date->format('Y');
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->first();

                if ($balance) {
                    $balance->decrement('used', min($balance->used, $leaveRequest->days));
                }

                // Delete or reset attendance marked as leave
                $period = CarbonPeriod::create($leaveRequest->from_date, '1 day', $leaveRequest->to_date);
                foreach ($period as $date) {
                    Attendance::where('employee_id', $leaveRequest->employee_id)
                        ->whereDate('date', $date->format('Y-m-d'))
                        ->where('status', AttendanceStatusEnum::LEAVE)
                        ->delete();
                }
            }

            $leaveRequest->update([
                'status' => LeaveRequestStatusEnum::CANCELLED,
            ]);

            return $leaveRequest->fresh();
        });
    }

    /**
     * Allocate yearly leave balances for employees with carry-forward rules.
     */
    public function allocateYearly(int $year, ?int $employeeId = null): int
    {
        $employeesQuery = User::whereHas('employeeDetail', function ($q) {
            $q->where('status', EmployeeStatusEnum::ACTIVE);
        });

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }

        $employees = $employeesQuery->get();
        $leaveTypes = LeaveType::active()->get();
        $processedCount = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $carried = 0.0;

                if ($leaveType->carry_forward) {
                    $prevBalance = LeaveBalance::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('year', $year - 1)
                        ->first();

                    if ($prevBalance && $prevBalance->remaining > 0) {
                        $maxCarry = (float) $leaveType->max_carry;
                        $carried = $maxCarry > 0 ? min($prevBalance->remaining, $maxCarry) : $prevBalance->remaining;
                    }
                }

                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                    ],
                    [
                        'allocated' => $leaveType->days_per_year,
                        'carried' => $carried,
                    ]
                );

                $processedCount++;
            }
        }

        return $processedCount;
    }

    /**
     * Get data for My Leaves page.
     */
    public function getMyLeavesData(int $employeeId, int $year): array
    {
        $leaveTypes = LeaveType::active()->get();

        // Ensure balances exist for all active types
        foreach ($leaveTypes as $type) {
            $this->getOrCreateBalance($employeeId, $type->id, $year);
        }

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get();

        $requests = LeaveRequest::with(['leaveType', 'approver'])
            ->where('employee_id', $employeeId)
            ->whereYear('from_date', $year)
            ->latest()
            ->paginate(15);

        return [
            'balances' => $balances,
            'requests' => $requests,
            'leaveTypes' => $leaveTypes,
            'year' => $year,
        ];
    }

    /**
     * Get leave requests approval queue.
     */
    public function getRequestsQueue(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveRequest::with([
            'employee.detail.department',
            'employee.detail.designation',
            'leaveType',
            'approver',
        ])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee.employeeDetail', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($empQ) use ($search) {
                        $empQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('employeeDetail', function ($d) use ($search) {
                                $d->where('emp_code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if (! empty($filters['year'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereYear('from_date', $filters['year'])
                    ->orWhereYear('to_date', $filters['year']);
            });
        }

        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereBetween('from_date', [$filters['from_date'], $filters['to_date']])
                    ->orWhereBetween('to_date', [$filters['from_date'], $filters['to_date']]);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get balances report across all employees for a given year.
     */
    public function getBalancesReport(int $year, array $filters = []): array
    {
        $leaveTypes = LeaveType::active()->get();

        $employeesQuery = User::with([
            'detail.department',
            'detail.designation',
            'leaveBalances' => fn ($q) => $q->where('year', $year),
        ])->whereHas('employeeDetail', function ($q) use ($filters) {
            $q->where('status', EmployeeStatusEnum::ACTIVE);

            if (! empty($filters['department_id'])) {
                $q->where('department_id', $filters['department_id']);
            }
        });

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('employeeDetail', fn ($d) => $d->where('emp_code', 'like', "%{$search}%"));
            });
        }

        $employees = $employeesQuery->orderBy('name')->paginate(20)->withQueryString();

        return [
            'year' => $year,
            'leaveTypes' => $leaveTypes,
            'employees' => $employees,
        ];
    }

    /**
     * Get leave events for calendar view.
     */
    public function getCalendarEvents(int $year, int $month, array $filters = []): array
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $query = LeaveRequest::with(['employee.detail.department', 'leaveType'])
            ->whereIn('status', [LeaveRequestStatusEnum::APPROVED, LeaveRequestStatusEnum::PENDING])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('from_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('to_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($inner) use ($startOfMonth, $endOfMonth) {
                        $inner->where('from_date', '<=', $startOfMonth)
                            ->where('to_date', '>=', $endOfMonth);
                    });
            });

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee.employeeDetail', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        return $query->get()->map(function (LeaveRequest $request) {
            return [
                'id' => $request->id,
                'title' => ($request->employee?->name ?? 'Employee') . ' - ' . ($request->leaveType?->name ?? 'Leave') . ($request->half_day ? ' (Half)' : ''),
                'start' => $request->from_date->format('Y-m-d'),
                'end' => $request->to_date->addDay()->format('Y-m-d'), // FullCalendar end date is exclusive
                'color' => $request->status === LeaveRequestStatusEnum::APPROVED ? ($request->leaveType?->color ?? '#10b981') : '#f59e0b',
                'status' => $request->status->value,
                'employee_name' => $request->employee?->name ?? '',
                'leave_type' => $request->leaveType?->name ?? '',
                'days' => $request->days,
                'reason' => $request->reason,
            ];
        })->toArray();
    }

    /**
     * Adjust leave balance for an employee.
     */
    public function adjustBalance(int $employeeId, int $leaveTypeId, int $year, float $allocated, float $carried, float $used): LeaveBalance
    {
        return LeaveBalance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'allocated' => $allocated,
                'carried' => $carried,
                'used' => $used,
            ]
        );
    }
}
