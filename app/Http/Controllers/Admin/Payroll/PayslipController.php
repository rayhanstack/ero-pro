<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Enums\PayslipStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\UpdatePayslipRequest;
use App\Models\Department;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PayslipController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    /**
     * Display a listing of payslips for a given period.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $periodId = $request->input('period_id');
        $period = $periodId
            ? PayrollPeriod::find($periodId)
            : PayrollPeriod::latest('year')->latest('month')->first();

        if (! $period) {
            return redirect()->route('payroll.periods.index')
                ->with('error', _trans('common.Please create or select a payroll period first.'));
        }

        $filters = $request->only(['search', 'status', 'department_id']);
        $payslips = $this->payrollService->getPaginatedPayslips($period, $filters, 15);
        $allPeriods = PayrollPeriod::orderByDesc('year')->orderByDesc('month')->get();
        $departments = Department::active()->orderBy('name')->get();
        $statuses = PayslipStatusEnum::cases();

        $stats = [
            'total' => $period->payslips()->count(),
            'approved' => $period->payslips()->where('status', PayslipStatusEnum::APPROVED->value)->count(),
            'paid' => $period->payslips()->where('status', PayslipStatusEnum::PAID->value)->count(),
            'total_net' => (float) $period->payslips()->sum('net_pay'),
            'total_basic' => (float) $period->payslips()->sum('basic'),
        ];

        $title = _trans('common.Payslips') . ' — ' . $period->formatted_period;

        return view('admin.payroll.payslips.index', compact(
            'title',
            'period',
            'payslips',
            'allPeriods',
            'departments',
            'statuses',
            'stats',
            'filters'
        ));
    }

    /**
     * Display the specified payslip detail.
     */
    public function show(Payslip $payslip): View
    {
        // Check authorization: admin with permission or owner of the payslip
        if (! Auth::user()->can('payroll.view') && $payslip->employee_id !== Auth::id()) {
            abort(403);
        }

        $payslip->loadMissing([
            'payrollPeriod',
            'employee.employeeDetail.department',
            'employee.employeeDetail.designation',
            'employee.primaryBankAccount',
            'items.component',
        ]);

        $title = _trans('common.Payslip') . ' #' . $payslip->payslip_number;

        return view('admin.payroll.payslips.show', compact('title', 'payslip'));
    }

    /**
     * Update the specified payslip adjustments (bonus, tax, overtime, absent deduction, note).
     */
    public function update(UpdatePayslipRequest $request, Payslip $payslip): RedirectResponse|JsonResponse
    {
        $updated = $this->payrollService->updatePayslip($payslip, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payslip adjustments updated successfully.'),
                'data' => $updated,
            ]);
        }

        return back()->with('success', _trans('common.Payslip adjustments updated successfully.'));
    }

    /**
     * Approve a single payslip.
     */
    public function approve(Request $request, Payslip $payslip): RedirectResponse|JsonResponse
    {
        $this->payrollService->approvePayslip($payslip);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payslip approved successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Payslip approved successfully.'));
    }

    /**
     * Bulk approve all draft payslips for a period.
     */
    public function bulkApprove(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse|JsonResponse
    {
        $count = $this->payrollService->bulkApprovePayslips($payrollPeriod);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Approved :count payslips.', ['count' => $count]),
            ]);
        }

        return back()->with('success', _trans('common.Approved :count payslips.', ['count' => $count]));
    }

    /**
     * Mark a payslip as Paid.
     */
    public function markPaid(Request $request, Payslip $payslip): RedirectResponse|JsonResponse
    {
        $paymentMethod = $request->input('payment_method', 'Bank Transfer');
        $this->payrollService->markPaid($payslip, $paymentMethod);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payslip marked as Paid. Finance expense hook triggered.'),
            ]);
        }

        return back()->with('success', _trans('common.Payslip marked as Paid. Finance expense hook triggered.'));
    }

    /**
     * Bulk mark all approved payslips in a period as Paid.
     */
    public function bulkMarkPaid(Request $request, PayrollPeriod $payrollPeriod): RedirectResponse|JsonResponse
    {
        $paymentMethod = $request->input('payment_method', 'Bank Transfer');
        $count = $this->payrollService->bulkMarkPaid($payrollPeriod, $paymentMethod);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Disbursed :count payslips.', ['count' => $count]),
            ]);
        }

        return back()->with('success', _trans('common.Disbursed :count payslips.', ['count' => $count]));
    }

    /**
     * Download PDF version of a payslip.
     */
    public function pdf(Payslip $payslip): Response
    {
        if (! Auth::user()->can('payroll.view') && $payslip->employee_id !== Auth::id()) {
            abort(403);
        }

        return $this->payrollService->generatePdf($payslip);
    }

    /**
     * Display "My Payslips" page for regular employee self-service.
     */
    public function myPayslips(Request $request): View
    {
        $employee = Auth::user();
        $payslips = $this->payrollService->getMyPayslips($employee, 12);
        $title = _trans('common.My Payslips');

        return view('admin.payroll.payslips.my', compact('title', 'payslips', 'employee'));
    }
}
