<?php

namespace Tests\Feature;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\RegularizationStatusEnum;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\Shift;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Database\Seeders\WeekendSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrUser;
    protected User $employeeUser;
    protected Shift $morningShift;
    protected Department $department;
    protected Designation $designation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ShiftSeeder::class,
            WeekendSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin User']);
        $this->superAdmin->assignRole('Super Admin');

        $this->hrUser = User::factory()->create(['name' => 'HR Manager User']);
        $this->hrUser->assignRole('HR');

        $this->department = Department::first();
        $this->designation = Designation::first();

        // 9:00 AM to 6:00 PM with 15 mins grace
        $this->morningShift = Shift::firstOrCreate(
            ['name' => 'General Morning Shift'],
            [
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'grace_minutes' => 15,
                'status' => 'active',
            ]
        );

        $this->employeeUser = User::factory()->create(['name' => 'Regular Employee']);
        $this->employeeUser->assignRole('Employee');

        EmployeeDetail::factory()->create([
            'user_id' => $this->employeeUser->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->morningShift->id,
        ]);
    }

    public function test_employee_check_in_on_time_marks_present_with_zero_late_minutes(): void
    {
        $attendanceService = app(AttendanceService::class);
        $checkInTime = Carbon::parse('2026-03-10 09:10:00'); // Within 15 min grace (09:00 + 15m = 09:15)

        $attendance = $attendanceService->checkIn($this->employeeUser, $checkInTime, '127.0.0.1');

        $this->assertNotNull($attendance);
        $this->assertEquals(AttendanceStatusEnum::PRESENT, $attendance->status);
        $this->assertEquals(0, $attendance->late_minutes);

        $saved = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', '2026-03-10')->first();
        $this->assertNotNull($saved);
        $this->assertEquals(AttendanceStatusEnum::PRESENT, $saved->status);
        $this->assertEquals(0, $saved->late_minutes);
    }

    public function test_employee_check_in_late_marks_late_with_correct_late_minutes(): void
    {
        $attendanceService = app(AttendanceService::class);
        // Checked in at 09:40 AM -> Shift starts at 09:00, Grace is 15 mins (deadline was 09:15).
        // Late minutes from 09:00 to 09:40 = 40 minutes.
        $checkInTime = Carbon::parse('2026-03-10 09:40:00');

        $attendance = $attendanceService->checkIn($this->employeeUser, $checkInTime, '127.0.0.1');

        $this->assertNotNull($attendance);
        $this->assertEquals(AttendanceStatusEnum::LATE, $attendance->status);
        $this->assertEquals(40, $attendance->late_minutes);

        $saved = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', '2026-03-10')->first();
        $this->assertNotNull($saved);
        $this->assertEquals(AttendanceStatusEnum::LATE, $saved->status);
        $this->assertEquals(40, $saved->late_minutes);
    }

    public function test_employee_check_out_calculates_work_and_overtime_minutes(): void
    {
        $attendanceService = app(AttendanceService::class);
        $checkInTime = Carbon::parse('2026-03-10 09:00:00');
        $attendanceService->checkIn($this->employeeUser, $checkInTime, '127.0.0.1');

        // Checked out at 19:30 (7:30 PM) -> Shift ends at 18:00 (6:00 PM).
        // Total work: 09:00 to 19:30 = 10.5 hours = 630 minutes.
        // Overtime: 18:00 to 19:30 = 1.5 hours = 90 minutes.
        $checkOutTime = Carbon::parse('2026-03-10 19:30:00');
        $attendance = $attendanceService->checkOut($this->employeeUser, $checkOutTime, '127.0.0.1');

        $this->assertEquals(630, $attendance->work_minutes);
        $this->assertEquals(90, $attendance->overtime_minutes);

        $saved = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', '2026-03-10')->first();
        $this->assertNotNull($saved);
        $this->assertEquals(630, $saved->work_minutes);
        $this->assertEquals(90, $saved->overtime_minutes);
    }

    public function test_employee_check_out_short_duration_marks_half_day(): void
    {
        $attendanceService = app(AttendanceService::class);
        $checkInTime = Carbon::parse('2026-03-10 09:00:00');
        $attendanceService->checkIn($this->employeeUser, $checkInTime, '127.0.0.1');

        // Only worked 2 hours (120 minutes) which is less than 4.0 hours half_day threshold
        $checkOutTime = Carbon::parse('2026-03-10 11:00:00');
        $attendance = $attendanceService->checkOut($this->employeeUser, $checkOutTime, '127.0.0.1');

        $this->assertEquals(120, $attendance->work_minutes);
        $this->assertEquals(AttendanceStatusEnum::HALF_DAY, $attendance->status);
    }

    public function test_my_attendance_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('attendances.my'));

        $response->assertStatus(200);
        $response->assertSee(_trans('common.My Attendance'));
        $response->assertSee(_trans('common.Monthly Overview'));
    }

    public function test_punch_endpoint_processes_check_in_and_check_out(): void
    {
        // Punch In
        $inResponse = $this->actingAs($this->employeeUser)->post(route('attendances.punch'), [
            'type' => 'in',
            'note' => 'Arrived at office',
        ]);
        $inResponse->assertSessionHas('success');
        $todayAtt = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', Carbon::today())->first();
        $this->assertNotNull($todayAtt);

        // Punch Out
        $outResponse = $this->actingAs($this->employeeUser)->post(route('attendances.punch'), [
            'type' => 'out',
            'note' => 'Leaving office',
        ]);
        $outResponse->assertSessionHas('success');

        // JSON status endpoint
        $statusResponse = $this->actingAs($this->employeeUser)->getJson(route('attendances.punch-status'));
        $statusResponse->assertStatus(200)
            ->assertJsonStructure(['checked_in', 'checked_out', 'status', 'work_duration']);
    }

    public function test_daily_attendance_page_can_be_rendered_with_filters(): void
    {
        $response = $this->actingAs($this->hrUser)->get(route('attendances.daily', [
            'date' => Carbon::today()->toDateString(),
            'department_id' => $this->department->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee(_trans('common.Daily Attendance'));
        $response->assertSee($this->employeeUser->name);
    }

    public function test_monthly_attendance_grid_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->hrUser)->get(route('attendances.monthly', [
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month,
        ]));

        $response->assertStatus(200);
        $response->assertSee(_trans('common.Monthly Attendance Sheet'));
        $response->assertSee($this->employeeUser->name);
    }

    public function test_hr_can_manually_add_and_update_attendance(): void
    {
        $date = Carbon::today()->toDateString();

        // HR adds manual attendance
        $response = $this->actingAs($this->hrUser)->post(route('attendances.store'), [
            'employee_id' => $this->employeeUser->id,
            'date' => $date,
            'status' => AttendanceStatusEnum::PRESENT->value,
            'check_in' => '09:00',
            'check_out' => '17:30',
            'note' => 'Manual entry by HR',
        ]);

        $response->assertSessionHas('success');
        $attendance = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', $date)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(AttendanceSourceEnum::MANUAL, $attendance->source);

        // HR updates attendance
        $updateResponse = $this->actingAs($this->hrUser)->put(route('attendances.update', $attendance), [
            'employee_id' => $this->employeeUser->id,
            'date' => $date,
            'status' => AttendanceStatusEnum::LATE->value,
            'check_in' => '10:00',
            'check_out' => '18:00',
            'note' => 'Updated by HR',
        ]);

        $updateResponse->assertSessionHas('success');
        $attendance->refresh();
        $this->assertEquals(AttendanceStatusEnum::LATE, $attendance->status);
    }

    public function test_employee_can_submit_regularization_and_hr_can_approve(): void
    {
        $date = Carbon::yesterday()->toDateString();

        // Employee requests regularization
        $regResponse = $this->actingAs($this->employeeUser)->post(route('attendances.regularizations.store'), [
            'date' => $date,
            'requested_in' => '09:00',
            'requested_out' => '18:00',
            'reason' => 'Forgot to punch in and out yesterday due to client meeting',
        ]);

        $regResponse->assertSessionHas('success');
        $reg = AttendanceRegularization::where('employee_id', $this->employeeUser->id)->whereDate('date', $date)->first();
        $this->assertNotNull($reg);
        $this->assertEquals(RegularizationStatusEnum::PENDING, $reg->status);

        // HR approves regularization
        $actionResponse = $this->actingAs($this->hrUser)->patch(route('attendances.regularizations.action', $reg), [
            'action' => 'approve',
            'admin_note' => 'Approved after verification',
        ]);

        $actionResponse->assertSessionHas('success');
        $reg->refresh();
        $this->assertEquals(RegularizationStatusEnum::APPROVED, $reg->status);
        $this->assertEquals($this->hrUser->id, $reg->approved_by);

        // Check attendance record was created/updated
        $savedAtt = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', $date)->first();
        $this->assertNotNull($savedAtt);
        $this->assertEquals(AttendanceStatusEnum::PRESENT, $savedAtt->status);
        $this->assertEquals(540, $savedAtt->work_minutes);
    }

    public function test_employee_regularization_rejection_flow(): void
    {
        $date = Carbon::yesterday()->toDateString();

        $reg = AttendanceRegularization::create([
            'employee_id' => $this->employeeUser->id,
            'date' => $date,
            'requested_in' => Carbon::parse($date . ' 09:00:00'),
            'requested_out' => Carbon::parse($date . ' 18:00:00'),
            'reason' => 'Missing attendance',
            'status' => RegularizationStatusEnum::PENDING,
        ]);

        $actionResponse = $this->actingAs($this->hrUser)->patch(route('attendances.regularizations.action', $reg), [
            'action' => 'reject',
            'admin_note' => 'Insufficient reason provided',
        ]);

        $actionResponse->assertSessionHas('success');
        $reg->refresh();
        $this->assertEquals(RegularizationStatusEnum::REJECTED, $reg->status);
    }

    public function test_mark_absent_artisan_command_fills_missing_attendance_records(): void
    {
        $date = '2026-03-10'; // A Tuesday (weekday)

        $this->artisan('attendance:mark-absent', ['--date' => $date])
            ->assertExitCode(0);

        // The active employee should now have an ABSENT record for that date
        $saved = Attendance::where('employee_id', $this->employeeUser->id)->whereDate('date', $date)->first();
        $this->assertNotNull($saved);
        $this->assertEquals(AttendanceStatusEnum::ABSENT, $saved->status);
    }

    public function test_unauthorized_user_cannot_access_attendance_management(): void
    {
        $unauthorizedUser = User::factory()->create();

        // Cannot view daily attendance
        $response = $this->actingAs($unauthorizedUser)->get(route('attendances.daily'));
        $response->assertStatus(403);

        // Cannot view monthly attendance
        $monthlyResponse = $this->actingAs($unauthorizedUser)->get(route('attendances.monthly'));
        $monthlyResponse->assertStatus(403);
    }
}
