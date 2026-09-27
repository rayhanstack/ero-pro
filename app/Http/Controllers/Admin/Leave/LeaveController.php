<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Enums\LeaveRequestStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\ActionLeaveRequest;
use App\Http\Requests\Leave\AdjustBalanceRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Leave\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveService $leaveService
    ) {}

    /**
     * Display logged-in employee's own leave dashboard and history.
     */
    public function my(Request $request): View
    {
        $user = Auth::user();
        $year = (int) $request->input('year', Carbon::now()->year);

        $data = $this->leaveService->getMyLeavesData($user->id, $year);

        return view('admin.leaves.my', array_merge($data, [
            'user' => $user,
        ]));
    }

    /**
     * Submit a new leave request.
     */
    public function apply(StoreLeaveRequest $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        $leaveRequest = $this->leaveService->apply($request->validated(), $user->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Leave request submitted successfully.'),
                'data' => $leaveRequest,
            ]);
        }

        return redirect()->route('leaves.my')->with('success', _trans('common.Leave request submitted successfully.'));
    }

    /**
     * Cancel a leave request.
     */
    public function cancel(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $user = Auth::user();

        // Check ownership or management permission
        if ($leave->employee_id !== $user->id && ! $user->can('leave.manage')) {
            abort(403, _trans('common.Unauthorized action.'));
        }

        if ($leave->status === LeaveRequestStatusEnum::CANCELLED) {
            return back()->with('error', _trans('common.Leave request is already cancelled.'));
        }

        $this->leaveService->cancel($leave, $user->id);

        return back()->with('success', _trans('common.Leave request cancelled successfully.'));
    }

    /**
     * Display the approval queue for HR and Managers.
     */
    public function requests(Request $request): View
    {
        $filters = $request->only(['status', 'leave_type_id', 'department_id', 'search', 'year', 'from_date', 'to_date']);
        $requests = $this->leaveService->getRequestsQueue($filters);

        $leaveTypes = LeaveType::active()->get();
        $departments = Department::active()->get();

        // Stats
        $stats = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::pending()->count(),
            'approved' => LeaveRequest::approved()->count(),
            'rejected' => LeaveRequest::rejected()->count(),
        ];

        return view('admin.leaves.requests', compact(
            'requests',
            'leaveTypes',
            'departments',
            'filters',
            'stats'
        ));
    }

    /**
     * Approve or reject a leave request.
     */
    public function action(ActionLeaveRequest $request, LeaveRequest $leave): RedirectResponse|JsonResponse
    {
        $action = $request->input('action');
        $remark = $request->input('remark');
        $approverId = Auth::id();

        if ($leave->status !== LeaveRequestStatusEnum::PENDING) {
            return back()->with('error', _trans('common.Only pending leave requests can be actioned.'));
        }

        if ($action === 'approve') {
            $this->leaveService->approve($leave, $approverId, $remark);
            $message = _trans('common.Leave request approved successfully.');
        } else {
            $this->leaveService->reject($leave, $approverId, $remark);
            $message = _trans('common.Leave request rejected.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Display employee leave balances report.
     */
    public function balances(Request $request): View
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $filters = $request->only(['department_id', 'search']);

        $reportData = $this->leaveService->getBalancesReport($year, $filters);
        $departments = Department::active()->get();

        return view('admin.leaves.balances', array_merge($reportData, [
            'departments' => $departments,
            'filters' => $filters,
        ]));
    }

    /**
     * Adjust leave balance for an employee (HR manual override).
     */
    public function adjustBalance(AdjustBalanceRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $this->leaveService->adjustBalance(
            (int) $validated['employee_id'],
            (int) $validated['leave_type_id'],
            (int) $validated['year'],
            (float) $validated['allocated'],
            (float) $validated['carried'],
            (float) $validated['used']
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => _trans('common.Leave balance adjusted successfully.'),
            ]);
        }

        return back()->with('success', _trans('common.Leave balance adjusted successfully.'));
    }

    /**
     * Display the visual leave calendar.
     */
    public function calendar(Request $request): View
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $filters = $request->only(['department_id', 'leave_type_id']);

        $events = $this->leaveService->getCalendarEvents($year, $month, $filters);
        $leaveTypes = LeaveType::active()->get();
        $departments = Department::active()->get();

        return view('admin.leaves.calendar', compact(
            'year',
            'month',
            'events',
            'leaveTypes',
            'departments',
            'filters'
        ));
    }

    /**
     * AJAX endpoint to calculate working days for dates in apply modal.
     */
    public function calculateDaysAjax(Request $request): JsonResponse
    {
        $user = Auth::user();
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date') ?: $fromDate;
        $halfDay = (bool) $request->input('half_day', false);
        $leaveTypeId = $request->input('leave_type_id');

        if (! $fromDate) {
            return response()->json(['days' => 0, 'valid' => false]);
        }

        $days = $this->leaveService->calculateDays($user->id, $fromDate, $toDate, $halfDay);

        $hasBalance = true;
        $remaining = 0;
        if ($leaveTypeId) {
            $year = (int) Carbon::parse($fromDate)->format('Y');
            $hasBalance = $this->leaveService->checkBalance($user->id, (int) $leaveTypeId, $days, $year);
            $balance = \App\Models\LeaveBalance::where('employee_id', $user->id)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->first();
            $remaining = $balance ? $balance->remaining : 0;
        }

        return response()->json([
            'days' => $days,
            'has_balance' => $hasBalance,
            'remaining_balance' => $remaining,
            'valid' => $days > 0,
        ]);
    }
}
