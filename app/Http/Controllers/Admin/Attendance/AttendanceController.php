<?php

namespace App\Http\Controllers\Admin\Attendance;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\RegularizationStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ActionRegularizationRequest;
use App\Http\Requests\Attendance\ManualAttendanceRequest;
use App\Http\Requests\Attendance\PunchAttendanceRequest;
use App\Http\Requests\Attendance\StoreRegularizationRequest;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Department;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display logged-in employee's own attendance view.
     */
    public function my(Request $request): View
    {
        $user = Auth::user();
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);

        $monthData = $this->attendanceService->getMyMonthAttendance($user, $year, $month);
        $todayAttendance = $this->attendanceService->getTodayStatus($user);
        $pendingRegularizations = AttendanceRegularization::forEmployee($user->id)->pending()->count();

        return view('admin.attendances.my', compact(
            'monthData',
            'todayAttendance',
            'pendingRegularizations',
            'year',
            'month',
            'user'
        ));
    }

    /**
     * Punch In / Punch Out action.
     */
    public function punch(PunchAttendanceRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $type = $request->input('type');
        $ip = $request->ip();
        $note = $request->input('note');

        if ($type === 'in') {
            $attendance = $this->attendanceService->checkIn($user, Carbon::now(), $ip, $note);
            $msg = _trans('common.Checked in successfully. Have a productive day!');
        } else {
            $attendance = $this->attendanceService->checkOut($user, Carbon::now(), $ip, $note);
            $msg = _trans('common.Checked out successfully. See you tomorrow!');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'attendance' => $attendance,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Get live punch status for topbar / dashboard widget (AJAX).
     */
    public function punchStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        $today = $this->attendanceService->getTodayStatus($user);

        $isCheckedIn = $today && $today->check_in !== null;
        $isCheckedOut = $today && $today->check_out !== null;

        $checkInFormatted = $today?->check_in_time;
        $checkOutFormatted = $today?->check_out_time;

        $liveSeconds = 0;
        if ($isCheckedIn && ! $isCheckedOut) {
            $liveSeconds = max(0, Carbon::now()->diffInSeconds($today->check_in));
        } elseif ($isCheckedIn && $isCheckedOut) {
            $liveSeconds = $today->work_minutes * 60;
        }

        return response()->json([
            'checked_in' => $isCheckedIn,
            'checked_out' => $isCheckedOut,
            'check_in_time' => $checkInFormatted,
            'check_out_time' => $checkOutFormatted,
            'status' => $today?->status?->value ?? 'not_marked',
            'status_label' => $today?->status?->label() ?? _trans('common.Not Marked'),
            'status_badge' => $today?->status?->badgeClass() ?? '',
            'live_seconds' => $liveSeconds,
            'work_duration' => $today?->work_duration_formatted ?? '0m',
        ]);
    }

    /**
     * Daily attendance list for HR / Management.
     */
    public function daily(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $search = $request->input('search');

        $dailyData = $this->attendanceService->getDailyAttendance($date, $departmentId, $search);
        $departments = Department::active()->orderBy('name')->get();
        $statuses = AttendanceStatusEnum::cases();

        return view('admin.attendances.daily', compact(
            'dailyData',
            'departments',
            'statuses',
            'date',
            'departmentId',
            'search'
        ));
    }

    /**
     * Monthly matrix grid for HR / Management.
     */
    public function monthly(Request $request): View
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $search = $request->input('search');

        $gridData = $this->attendanceService->getMonthlyGrid($year, $month, $departmentId, $search);
        $departments = Department::active()->orderBy('name')->get();
        $statuses = AttendanceStatusEnum::cases();

        return view('admin.attendances.monthly', compact(
            'gridData',
            'departments',
            'statuses',
            'year',
            'month',
            'departmentId',
            'search'
        ));
    }

    /**
     * Store manual attendance record by HR.
     */
    public function store(ManualAttendanceRequest $request): RedirectResponse
    {
        $employee = User::findOrFail($request->input('employee_id'));
        $date = $request->input('date');
        $status = AttendanceStatusEnum::from($request->input('status'));

        $this->attendanceService->markStatus($employee, $date, $status, $request->validated());

        return back()->with('success', _trans('common.Attendance record saved successfully.'));
    }

    /**
     * Update manual attendance record by HR.
     */
    public function update(ManualAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $employee = $attendance->employee;
        $date = $request->input('date', $attendance->date->toDateString());
        $status = AttendanceStatusEnum::from($request->input('status'));

        $this->attendanceService->markStatus($employee, $date, $status, $request->validated());

        return back()->with('success', _trans('common.Attendance record updated successfully.'));
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return back()->with('success', _trans('common.Attendance record deleted successfully.'));
    }

    /**
     * List Regularization Requests.
     */
    public function regularizations(Request $request): View
    {
        $user = Auth::user();
        $isHrOrAdmin = $user->can('attendance.manage');
        $statusFilter = $request->input('status', 'all');

        $query = AttendanceRegularization::with(['employee.detail.department', 'approver'])
            ->latest('id');

        if (! $isHrOrAdmin) {
            $query->forEmployee($user->id);
        }

        if ($statusFilter !== 'all' && in_array($statusFilter, RegularizationStatusEnum::values(), true)) {
            $query->where('status', $statusFilter);
        }

        $regularizations = $query->paginate(15)->withQueryString();
        $pendingCount = AttendanceRegularization::pending()->count();

        return view('admin.attendances.regularizations', compact(
            'regularizations',
            'isHrOrAdmin',
            'statusFilter',
            'pendingCount'
        ));
    }

    /**
     * Submit a regularization request.
     */
    public function storeRegularization(StoreRegularizationRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $date = $request->input('date');

        $reqIn = $request->filled('requested_in') ? Carbon::parse($date . ' ' . $request->input('requested_in')) : null;
        $reqOut = $request->filled('requested_out') ? Carbon::parse($date . ' ' . $request->input('requested_out')) : null;

        AttendanceRegularization::create([
            'employee_id' => $user->id,
            'date' => $date,
            'requested_in' => $reqIn,
            'requested_out' => $reqOut,
            'reason' => $request->input('reason'),
            'status' => RegularizationStatusEnum::PENDING,
        ]);

        return back()->with('success', _trans('common.Regularization request submitted successfully.'));
    }

    /**
     * Approve or reject a regularization request.
     */
    public function actionRegularization(ActionRegularizationRequest $request, AttendanceRegularization $regularization): RedirectResponse
    {
        $action = $request->input('action');
        $adminNote = $request->input('admin_note');

        $this->attendanceService->regularize($regularization, $action, Auth::user(), $adminNote);

        $msg = $action === 'approve'
            ? _trans('common.Regularization request approved and attendance updated.')
            : _trans('common.Regularization request rejected.');

        return back()->with('success', $msg);
    }
}
