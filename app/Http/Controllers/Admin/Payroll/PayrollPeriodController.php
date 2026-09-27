<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Enums\PayrollPeriodStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StorePayrollPeriodRequest;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollPeriodController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    /**
     * Display a listing of payroll periods.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['year', 'status']);

        $periods = $this->payrollService->getPaginatedPeriods($filters, 12);
        $stats = $this->payrollService->getPeriodStats();
        $statuses = PayrollPeriodStatusEnum::cases();
        $years = range(date('Y') + 1, 2024);

        $title = _trans('common.Payroll Periods');

        return view('admin.payroll.periods.index', compact(
            'title',
            'periods',
            'stats',
            'filters',
            'statuses',
            'years'
        ));
    }

    /**
     * Store a newly created payroll period in storage.
     */
    public function store(StorePayrollPeriodRequest $request): RedirectResponse|JsonResponse
    {
        $period = $this->payrollService->createPeriod($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payroll period created successfully.'),
                'data' => $period,
                'redirect' => route('payroll.periods.index'),
            ]);
        }

        return redirect()->route('payroll.periods.index')
            ->with('success', _trans('common.Payroll period created successfully.'));
    }

    /**
     * Generate payroll for all active employees for this period.
     */
    public function generate(Request $request, PayrollPeriod $period): RedirectResponse|JsonResponse
    {
        try {
            $count = $this->payrollService->generate($period);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => _trans('common.Generated :count payslips for :period.', [
                        'count' => $count,
                        'period' => $period->formatted_period,
                    ]),
                    'redirect' => route('payroll.payslips.index', ['period_id' => $period->id]),
                ]);
            }

            return redirect()->route('payroll.payslips.index', ['period_id' => $period->id])
                ->with('success', _trans('common.Generated :count payslips for :period.', [
                    'count' => $count,
                    'period' => $period->formatted_period,
                ]));
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Lock the payroll period.
     */
    public function lock(Request $request, PayrollPeriod $period): RedirectResponse|JsonResponse
    {
        $this->payrollService->lockPeriod($period);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payroll period locked successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Payroll period locked successfully.'));
    }

    /**
     * Bulk approve all draft payslips for a period.
     */
    public function bulkApprove(Request $request, PayrollPeriod $period): RedirectResponse|JsonResponse
    {
        $count = $this->payrollService->bulkApprovePayslips($period);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Approved :count payslips.', ['count' => $count]),
            ]);
        }

        return back()->with('success', _trans('common.Approved :count payslips.', ['count' => $count]));
    }

    /**
     * Bulk mark all approved payslips in a period as Paid.
     */
    public function bulkMarkPaid(Request $request, PayrollPeriod $period): RedirectResponse|JsonResponse
    {
        $paymentMethod = $request->input('payment_method', 'Bank Transfer');
        $count = $this->payrollService->bulkMarkPaid($period, $paymentMethod);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Disbursed :count payslips.', ['count' => $count]),
            ]);
        }

        return back()->with('success', _trans('common.Disbursed :count payslips.', ['count' => $count]));
    }

    /**
     * Delete a draft payroll period.
     */
    public function destroy(Request $request, PayrollPeriod $period): RedirectResponse|JsonResponse
    {
        if ($period->status === PayrollPeriodStatusEnum::LOCKED) {
            return back()->with('error', _trans('common.Cannot delete a locked payroll period.'));
        }

        $period->payslips()->delete();
        $period->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Payroll period deleted successfully.'),
            ]);
        }

        return redirect()->route('payroll.periods.index')
            ->with('success', _trans('common.Payroll period deleted successfully.'));
    }
}
