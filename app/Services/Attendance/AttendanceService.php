<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\RegularizationStatusEnum;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use App\Services\Hr\CalendarService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    /**
     * Get attendance record of an employee for today.
     */
    public function getTodayStatus(User $employee): ?Attendance
    {
        return Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();
    }

    /**
     * Process employee punch-in (check in).
     */
    public function checkIn(User $employee, ?Carbon $time = null, ?string $ip = null, ?string $note = null): Attendance
    {
        $time = $time ?? Carbon::now();
        $dateStr = $time->toDateString();

        return DB::transaction(function () use ($employee, $time, $dateStr, $ip, $note) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            if (! $attendance) {
                $attendance = new Attendance([
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                ]);
            }

            // If already checked in, return existing record
            if ($attendance->exists && $attendance->check_in !== null) {
                return $attendance;
            }

            // Determine shift and grace time
            $shift = $employee->shift ?? $employee->detail?->shift;
            $startTimeStr = $shift?->start_time ?? globalSetting('office_start', '09:00');
            $graceMinutes = $shift?->grace_minutes ?? (int) globalSetting('grace_minutes', 15);

            // Calculate late minutes
            $shiftStart = Carbon::parse($dateStr . ' ' . $startTimeStr);
            $graceDeadline = $shiftStart->copy()->addMinutes($graceMinutes);

            $lateMinutes = 0;
            $status = AttendanceStatusEnum::PRESENT;

            if ($time->gt($graceDeadline)) {
                $lateMinutes = max(0, (int) $shiftStart->diffInMinutes($time, false));
                $status = AttendanceStatusEnum::LATE;
            }

            $attendance->fill([
                'check_in' => $time,
                'late_minutes' => $lateMinutes,
                'status' => $status,
                'source' => AttendanceSourceEnum::WEB,
                'ip' => $ip,
                'note' => $note,
            ]);

            $attendance->save();

            return $attendance;
        });
    }

    /**
     * Process employee punch-out (check out).
     */
    public function checkOut(User $employee, ?Carbon $time = null, ?string $ip = null, ?string $note = null): Attendance
    {
        $time = $time ?? Carbon::now();
        $dateStr = $time->toDateString();

        return DB::transaction(function () use ($employee, $time, $dateStr, $ip, $note) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            if (! $attendance) {
                $attendance = new Attendance([
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                ]);
            }

            if (! $attendance->exists || ! $attendance->check_in) {
                // If checking out without check in, set check-in as well
                $attendance->check_in = $time;
                $attendance->status = AttendanceStatusEnum::PRESENT;
            }

            $attendance->check_out = $time;

            // Calculate work minutes
            $checkIn = Carbon::parse($attendance->check_in);
            $workMinutes = max(0, (int) $checkIn->diffInMinutes($time, false));
            $attendance->work_minutes = $workMinutes;

            // Determine shift for overtime
            $shift = $employee->shift ?? $employee->detail?->shift;
            $endTimeStr = $shift?->end_time ?? globalSetting('office_end', '18:00');
            $overtimeEnabled = (bool) globalSetting('overtime_enabled', true);

            $overtimeMinutes = 0;
            if ($overtimeEnabled && $endTimeStr) {
                $shiftEnd = Carbon::parse($dateStr . ' ' . $endTimeStr);
                if ($time->gt($shiftEnd)) {
                    $overtimeMinutes = max(0, (int) $shiftEnd->diffInMinutes($time, false));
                }
            }
            $attendance->overtime_minutes = $overtimeMinutes;

            // Check half-day threshold
            $halfDayHours = (float) globalSetting('half_day_hours', 4.0);
            $halfDayMinutes = (int) ($halfDayHours * 60);

            if ($halfDayMinutes > 0 && $workMinutes < $halfDayMinutes && $attendance->status !== AttendanceStatusEnum::ABSENT) {
                $attendance->status = AttendanceStatusEnum::HALF_DAY;
            }

            if ($ip) {
                $attendance->ip = $ip;
            }
            if ($note) {
                $attendance->note = $attendance->note ? ($attendance->note . ' | ' . $note) : $note;
            }

            $attendance->save();

            return $attendance;
        });
    }

    /**
     * Manually mark or update attendance by HR / Manager.
     */
    public function markStatus(User $employee, string|Carbon $date, AttendanceStatusEnum $status, array $data = []): Attendance
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

        return DB::transaction(function () use ($employee, $dateStr, $status, $data) {
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->first();

            if (! $attendance) {
                $attendance = new Attendance([
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                ]);
            }

            $checkIn = ! empty($data['check_in']) ? Carbon::parse($dateStr . ' ' . $data['check_in']) : null;
            $checkOut = ! empty($data['check_out']) ? Carbon::parse($dateStr . ' ' . $data['check_out']) : null;

            $workMinutes = (int) ($data['work_minutes'] ?? 0);
            $lateMinutes = (int) ($data['late_minutes'] ?? 0);
            $overtimeMinutes = (int) ($data['overtime_minutes'] ?? 0);

            // Compute automatic minutes if checkIn and checkOut provided
            if ($checkIn && $checkOut && ! isset($data['work_minutes'])) {
                $workMinutes = max(0, (int) $checkIn->diffInMinutes($checkOut, false));
            }

            if ($checkIn && ! isset($data['late_minutes'])) {
                $shift = $employee->shift ?? $employee->detail?->shift;
                $startTimeStr = $shift?->start_time ?? globalSetting('office_start', '09:00');
                $graceMinutes = $shift?->grace_minutes ?? (int) globalSetting('grace_minutes', 15);
                $shiftStart = Carbon::parse($dateStr . ' ' . $startTimeStr);
                $graceDeadline = $shiftStart->copy()->addMinutes($graceMinutes);

                if ($checkIn->gt($graceDeadline)) {
                    $lateMinutes = max(0, (int) $shiftStart->diffInMinutes($checkIn, false));
                }
            }

            $attendance->fill([
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'work_minutes' => $workMinutes,
                'late_minutes' => $lateMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'status' => $status,
                'source' => $data['source'] ?? AttendanceSourceEnum::MANUAL,
                'note' => $data['note'] ?? null,
                'ip' => $data['ip'] ?? null,
            ]);

            $attendance->save();

            return $attendance;
        });
    }

    /**
     * Get Daily attendance list for HR / Admin.
     */
    public function getDailyAttendance(string|Carbon $date, ?int $departmentId = null, ?string $search = null): array
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

        $query = User::with([
            'detail.department',
            'detail.designation',
            'detail.shift',
            'attendances' => fn ($q) => $q->whereDate('date', $dateStr),
        ])->where('status', EmployeeStatusEnum::ACTIVE->value);

        if ($departmentId) {
            $query->whereHas('detail', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($search) {
            $query->search($search);
        }

        $employees = $query->orderBy('name')->get();

        return [
            'date' => $dateStr,
            'is_weekend' => $this->calendarService->isWeekend($dateStr),
            'is_holiday' => $this->calendarService->isHoliday($dateStr),
            'employees' => $employees,
            'summary' => $this->getDailySummary($dateStr, $departmentId),
        ];
    }

    /**
     * Get summary KPI badges for a specific day.
     */
    public function getDailySummary(string|Carbon $date, ?int $departmentId = null): array
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;

        $userQuery = User::where('status', EmployeeStatusEnum::ACTIVE->value);
        if ($departmentId) {
            $userQuery->whereHas('detail', fn ($q) => $q->where('department_id', $departmentId));
        }
        $totalEmployees = $userQuery->count();

        $attQuery = Attendance::whereDate('date', $dateStr);
        if ($departmentId) {
            $attQuery->whereHas('employee.detail', fn ($q) => $q->where('department_id', $departmentId));
        }

        $attendances = $attQuery->get();

        $present = $attendances->where('status', AttendanceStatusEnum::PRESENT)->count();
        $late = $attendances->where('status', AttendanceStatusEnum::LATE)->count();
        $absent = $attendances->where('status', AttendanceStatusEnum::ABSENT)->count();
        $leave = $attendances->where('status', AttendanceStatusEnum::LEAVE)->count();
        $holiday = $attendances->where('status', AttendanceStatusEnum::HOLIDAY)->count();
        $weekend = $attendances->where('status', AttendanceStatusEnum::WEEKEND)->count();
        $halfDay = $attendances->where('status', AttendanceStatusEnum::HALF_DAY)->count();

        $totalPresentLike = $present + $late + $halfDay;
        $rate = $totalEmployees > 0 ? round(($totalPresentLike / $totalEmployees) * 100, 1) : 0;

        return [
            'total' => $totalEmployees,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'leave' => $leave,
            'holiday' => $holiday,
            'weekend' => $weekend,
            'half_day' => $halfDay,
            'attendance_rate' => $rate,
        ];
    }

    /**
     * Get monthly matrix data for all employees.
     */
    public function getMonthlyGrid(int $year, int $month, ?int $departmentId = null, ?string $search = null): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $current = Carbon::createFromDate($year, $month, $d);
            $days[$d] = [
                'day' => $d,
                'date' => $current->toDateString(),
                'day_name' => $current->format('D'),
                'is_weekend' => $this->calendarService->isWeekend($current),
                'is_holiday' => $this->calendarService->isHoliday($current),
            ];
        }

        $query = User::with([
            'detail.department',
            'detail.designation',
            'attendances' => fn ($q) => $q->whereYear('date', $year)->whereMonth('date', $month),
        ])->where('status', EmployeeStatusEnum::ACTIVE->value);

        if ($departmentId) {
            $query->whereHas('detail', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($search) {
            $query->search($search);
        }

        $employees = $query->orderBy('name')->get();

        $matrix = [];
        foreach ($employees as $employee) {
            $attByDay = [];
            $records = $employee->attendances->keyBy(fn ($a) => (int) Carbon::parse($a->date)->format('j'));

            $counts = [
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'leave' => 0,
                'holiday' => 0,
                'weekend' => 0,
                'half_day' => 0,
                'total_work_minutes' => 0,
                'total_overtime_minutes' => 0,
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $att = $records->get($d);
                if ($att) {
                    $attByDay[$d] = $att;
                    $statusKey = $att->status instanceof AttendanceStatusEnum ? $att->status->value : $att->status;
                    if (isset($counts[$statusKey])) {
                        $counts[$statusKey]++;
                    }
                    $counts['total_work_minutes'] += $att->work_minutes;
                    $counts['total_overtime_minutes'] += $att->overtime_minutes;
                } else {
                    $attByDay[$d] = null;
                }
            }

            $matrix[] = [
                'employee' => $employee,
                'attendances' => $attByDay,
                'counts' => $counts,
                'work_hours_formatted' => round($counts['total_work_minutes'] / 60, 1),
                'overtime_hours_formatted' => round($counts['total_overtime_minutes'] / 60, 1),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $startDate->format('F Y'),
            'days' => $days,
            'matrix' => $matrix,
        ];
    }

    /**
     * Get monthly attendance records for a specific employee (My Attendance).
     */
    public function getMyMonthAttendance(User $employee, int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->get();

        $totalPresent = $attendances->where('status', AttendanceStatusEnum::PRESENT)->count();
        $totalLate = $attendances->where('status', AttendanceStatusEnum::LATE)->count();
        $totalAbsent = $attendances->where('status', AttendanceStatusEnum::ABSENT)->count();
        $totalLeave = $attendances->where('status', AttendanceStatusEnum::LEAVE)->count();
        $totalHalfDay = $attendances->where('status', AttendanceStatusEnum::HALF_DAY)->count();
        $totalWorkMinutes = $attendances->sum('work_minutes');
        $totalOvertimeMinutes = $attendances->sum('overtime_minutes');

        $workingDays = $this->calendarService->workingDaysBetween($startDate, $endDate);

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $startDate->format('F Y'),
            'attendances' => $attendances,
            'stats' => [
                'working_days' => $workingDays,
                'present' => $totalPresent,
                'late' => $totalLate,
                'absent' => $totalAbsent,
                'leave' => $totalLeave,
                'half_day' => $totalHalfDay,
                'work_hours' => round($totalWorkMinutes / 60, 1),
                'overtime_hours' => round($totalOvertimeMinutes / 60, 1),
            ],
        ];
    }

    /**
     * Process Regularization request approval or rejection.
     */
    public function regularize(AttendanceRegularization $regularization, string $action, ?User $approver = null, ?string $adminNote = null): AttendanceRegularization
    {
        return DB::transaction(function () use ($regularization, $action, $approver, $adminNote) {
            $isApproved = $action === 'approve';

            $regularization->update([
                'status' => $isApproved ? RegularizationStatusEnum::APPROVED : RegularizationStatusEnum::REJECTED,
                'approved_by' => $approver?->id,
                'admin_note' => $adminNote,
            ]);

            if ($isApproved) {
                $employee = $regularization->employee;
                $dateStr = $regularization->date->toDateString();

                $attendance = Attendance::where('employee_id', $regularization->employee_id)
                    ->whereDate('date', $dateStr)
                    ->first();

                if (! $attendance) {
                    $attendance = new Attendance([
                        'employee_id' => $regularization->employee_id,
                        'date' => $dateStr,
                    ]);
                }

                $checkIn = $regularization->requested_in;
                $checkOut = $regularization->requested_out;

                $workMinutes = 0;
                if ($checkIn && $checkOut) {
                    $workMinutes = max(0, (int) Carbon::parse($checkIn)->diffInMinutes(Carbon::parse($checkOut), false));
                }

                $lateMinutes = 0;
                $status = AttendanceStatusEnum::PRESENT;
                if ($checkIn) {
                    $shift = $employee->shift ?? $employee->detail?->shift;
                    $startTimeStr = $shift?->start_time ?? globalSetting('office_start', '09:00');
                    $graceMinutes = $shift?->grace_minutes ?? (int) globalSetting('grace_minutes', 15);
                    $shiftStart = Carbon::parse($dateStr . ' ' . $startTimeStr);
                    $graceDeadline = $shiftStart->copy()->addMinutes($graceMinutes);

                    if (Carbon::parse($checkIn)->gt($graceDeadline)) {
                        $lateMinutes = max(0, (int) $shiftStart->diffInMinutes(Carbon::parse($checkIn), false));
                        $status = AttendanceStatusEnum::LATE;
                    }
                }

                $overtimeMinutes = 0;
                if ($checkOut) {
                    $shift = $employee->shift ?? $employee->detail?->shift;
                    $endTimeStr = $shift?->end_time ?? globalSetting('office_end', '18:00');
                    $overtimeEnabled = (bool) globalSetting('overtime_enabled', true);
                    if ($overtimeEnabled && $endTimeStr) {
                        $shiftEnd = Carbon::parse($dateStr . ' ' . $endTimeStr);
                        if (Carbon::parse($checkOut)->gt($shiftEnd)) {
                            $overtimeMinutes = max(0, (int) $shiftEnd->diffInMinutes(Carbon::parse($checkOut), false));
                        }
                    }
                }

                $attendance->fill([
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'work_minutes' => $workMinutes,
                    'late_minutes' => $lateMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'status' => $status,
                    'source' => AttendanceSourceEnum::MANUAL,
                    'note' => 'Regularized: ' . $regularization->reason,
                ]);

                $attendance->save();
            }

            return $regularization;
        });
    }

    /**
     * Mark default/absent/weekend/holiday records for a specific date (used by Cron/Command).
     */
    public function markDailyAbsentRecords(?Carbon $date = null): int
    {
        $date = $date ?? today();
        $dateStr = $date->toDateString();

        $isWeekend = $this->calendarService->isWeekend($date);
        $isHoliday = $this->calendarService->isHoliday($date);

        $defaultStatus = AttendanceStatusEnum::ABSENT;
        if ($isHoliday) {
            $defaultStatus = AttendanceStatusEnum::HOLIDAY;
        } elseif ($isWeekend) {
            $defaultStatus = AttendanceStatusEnum::WEEKEND;
        }

        $activeEmployees = User::where('status', EmployeeStatusEnum::ACTIVE->value)->get();
        $count = 0;

        foreach ($activeEmployees as $employee) {
            $exists = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $dateStr)
                ->exists();

            if (! $exists) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                    'status' => $defaultStatus,
                    'source' => AttendanceSourceEnum::MANUAL,
                    'note' => 'System Automated: ' . $defaultStatus->label(),
                ]);
                $count++;
            }
        }

        return $count;
    }
}
