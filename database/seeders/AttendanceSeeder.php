<?php

namespace Database\Seeders;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\RegularizationStatusEnum;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\User;
use App\Services\Hr\CalendarService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $calendarService = app(CalendarService::class);
        $employees = User::where('status', EmployeeStatusEnum::ACTIVE->value)->get();
        $today = Carbon::today();

        // Seed attendance for the last 20 days up to today
        for ($i = 20; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateStr = $date->toDateString();
            $isWeekend = $calendarService->isWeekend($date);
            $isHoliday = $calendarService->isHoliday($date);

            foreach ($employees as $index => $employee) {
                if ($isHoliday) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $dateStr],
                        [
                            'status' => AttendanceStatusEnum::HOLIDAY,
                            'source' => AttendanceSourceEnum::MANUAL,
                            'note' => 'Public Holiday',
                        ]
                    );
                } elseif ($isWeekend) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $dateStr],
                        [
                            'status' => AttendanceStatusEnum::WEEKEND,
                            'source' => AttendanceSourceEnum::MANUAL,
                            'note' => 'Weekend',
                        ]
                    );
                } else {
                    // Working day: distribute present / late / absent / half_day
                    $rand = ($employee->id + $i) % 10;

                    if ($rand === 0) {
                        // Absent
                        Attendance::updateOrCreate(
                            ['employee_id' => $employee->id, 'date' => $dateStr],
                            [
                                'status' => AttendanceStatusEnum::ABSENT,
                                'source' => AttendanceSourceEnum::MANUAL,
                                'note' => 'Unexcused Absence',
                            ]
                        );
                    } elseif ($rand === 1) {
                        // On Leave
                        Attendance::updateOrCreate(
                            ['employee_id' => $employee->id, 'date' => $dateStr],
                            [
                                'status' => AttendanceStatusEnum::LEAVE,
                                'source' => AttendanceSourceEnum::MANUAL,
                                'note' => 'Approved Annual Leave',
                            ]
                        );
                    } elseif ($rand === 2) {
                        // Late Arrival
                        $checkIn = Carbon::parse($dateStr . ' 09:35:00');
                        $checkOut = Carbon::parse($dateStr . ' 18:15:00');
                        $workMinutes = (int) $checkIn->diffInMinutes($checkOut);
                        $lateMinutes = 35; // 35 min late past 9:00

                        Attendance::updateOrCreate(
                            ['employee_id' => $employee->id, 'date' => $dateStr],
                            [
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'work_minutes' => $workMinutes,
                                'late_minutes' => $lateMinutes,
                                'overtime_minutes' => 15,
                                'status' => AttendanceStatusEnum::LATE,
                                'source' => AttendanceSourceEnum::WEB,
                                'note' => 'Traffic delay',
                            ]
                        );
                    } else {
                        // Present (On Time)
                        $inMin = rand(45, 58); // 8:45 to 8:58
                        $outMin = rand(0, 45); // 18:00 to 18:45
                        $checkIn = Carbon::parse($dateStr . ' 08:' . str_pad((string) $inMin, 2, '0', STR_PAD_LEFT) . ':00');
                        $checkOut = Carbon::parse($dateStr . ' 18:' . str_pad((string) $outMin, 2, '0', STR_PAD_LEFT) . ':00');
                        $workMinutes = (int) $checkIn->diffInMinutes($checkOut);
                        $overtime = $outMin;

                        Attendance::updateOrCreate(
                            ['employee_id' => $employee->id, 'date' => $dateStr],
                            [
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'work_minutes' => $workMinutes,
                                'late_minutes' => 0,
                                'overtime_minutes' => $overtime,
                                'status' => AttendanceStatusEnum::PRESENT,
                                'source' => AttendanceSourceEnum::WEB,
                            ]
                        );
                    }
                }
            }
        }

        // Seed a few regularization requests
        $sampleEmp = $employees->first();
        if ($sampleEmp) {
            $yesterday = $today->copy()->subDays(2);
            AttendanceRegularization::updateOrCreate(
                ['employee_id' => $sampleEmp->id, 'date' => $yesterday->toDateString()],
                [
                    'requested_in' => Carbon::parse($yesterday->toDateString() . ' 09:00:00'),
                    'requested_out' => Carbon::parse($yesterday->toDateString() . ' 18:00:00'),
                    'reason' => 'Forgot to punch in because of urgent client call at morning arrival',
                    'status' => RegularizationStatusEnum::PENDING,
                ]
            );
        }
    }
}
