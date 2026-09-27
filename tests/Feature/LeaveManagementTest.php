<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatusEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\LeaveRequestStatusEnum;
use App\Enums\StatusEnum;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Weekend;
use App\Notifications\LeaveStatusNotification;
use App\Services\Hr\CalendarService;
use App\Services\Leave\LeaveService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected LeaveType $leaveType;
    protected LeaveService $leaveService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        // Configure standard Friday weekend
        Weekend::truncate();
        for ($i = 0; $i < 7; $i++) {
            Weekend::create([
                'day_of_week' => $i,
                'name' => Carbon::now()->startOfWeek()->addDays($i)->format('l'),
                'is_weekend' => ($i === 5), // Friday
            ]);
        }

        app(CalendarService::class)->clearCache();

        $this->admin = User::factory()->create(['status' => EmployeeStatusEnum::ACTIVE]);
        $this->admin->assignRole('Super Admin');

        $this->employee = User::factory()->create(['status' => EmployeeStatusEnum::ACTIVE]);
        $this->employee->assignRole('Employee');

        $this->leaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'days_per_year' => 10,
            'is_paid' => true,
            'carry_forward' => false,
            'max_carry' => 0,
            'status' => StatusEnum::ACTIVE,
            'color' => '#3b82f6',
        ]);

        $this->leaveService = app(LeaveService::class);
    }

    public function test_calculate_days_excludes_weekends_and_holidays(): void
    {
        // Setup a holiday on Tuesday
        $monday = Carbon::parse('2026-10-05'); // Monday
        $tuesday = Carbon::parse('2026-10-06'); // Tuesday
        $sunday = Carbon::parse('2026-10-11'); // Sunday (7 days span: Mon-Sun)

        Holiday::create([
            'title' => 'National Holiday',
            'from_date' => $tuesday->format('Y-m-d'),
            'to_date' => $tuesday->format('Y-m-d'),
            'type' => \App\Enums\HolidayTypeEnum::PUBLIC,
            'status' => StatusEnum::ACTIVE,
        ]);

        app(CalendarService::class)->clearCache();

        // Range: Monday to Sunday = 7 calendar days.
        // Friday is weekend (1 day excluded). Tuesday is holiday (1 day excluded).
        // Total working days should be 5.
        $days = $this->leaveService->calculateDays($this->employee->id, $monday, $sunday);

        $this->assertEquals(5.0, $days);
    }

    public function test_calculate_days_for_half_day(): void
    {
        $monday = Carbon::parse('2026-10-05'); // Monday (working day)
        $friday = Carbon::parse('2026-10-09'); // Friday (weekend)

        $halfDayMon = $this->leaveService->calculateDays($this->employee->id, $monday, $monday, true);
        $halfDayFri = $this->leaveService->calculateDays($this->employee->id, $friday, $friday, true);

        $this->assertEquals(0.5, $halfDayMon);
        $this->assertEquals(0.0, $halfDayFri);
    }

    public function test_leave_balance_check_and_deduction(): void
    {
        $year = 2026;

        LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => $year,
            'allocated' => 10,
            'used' => 2,
            'carried' => 0,
        ]);

        $hasBalance5 = $this->leaveService->checkBalance($this->employee->id, $this->leaveType->id, 5, $year);
        $hasBalance9 = $this->leaveService->checkBalance($this->employee->id, $this->leaveType->id, 9, $year);

        $this->assertTrue($hasBalance5);
        $this->assertFalse($hasBalance9);
    }

    public function test_employee_can_apply_for_leave(): void
    {
        $this->leaveService->getOrCreateBalance($this->employee->id, $this->leaveType->id, 2026);

        $monday = Carbon::parse('2026-10-05');
        $wednesday = Carbon::parse('2026-10-07');

        $response = $this->actingAs($this->employee)->post(route('leaves.apply'), [
            'leave_type_id' => $this->leaveType->id,
            'from_date' => $monday->format('Y-m-d'),
            'to_date' => $wednesday->format('Y-m-d'),
            'half_day' => 0,
            'reason' => 'Family vacation trip',
        ]);

        $response->assertRedirect(route('leaves.my'));
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'days' => 3.0,
            'status' => LeaveRequestStatusEnum::PENDING->value,
        ]);
    }

    public function test_manager_can_approve_leave_request_and_marks_attendance_and_deducts_balance(): void
    {
        Notification::fake();

        $balance = LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'year' => 2026,
            'allocated' => 10,
            'used' => 0,
            'carried' => 0,
        ]);

        $monday = Carbon::parse('2026-10-05');
        $tuesday = Carbon::parse('2026-10-06');

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => $monday->format('Y-m-d'),
            'to_date' => $tuesday->format('Y-m-d'),
            'days' => 2.0,
            'half_day' => false,
            'reason' => 'Doctor appointment',
            'status' => LeaveRequestStatusEnum::PENDING,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('leaves.action', $leaveRequest), [
            'action' => 'approve',
            'remark' => 'Approved as requested',
        ]);

        $response->assertRedirect();

        // 1. Status updated
        $this->assertEquals(LeaveRequestStatusEnum::APPROVED, $leaveRequest->fresh()->status);
        $this->assertEquals($this->admin->id, $leaveRequest->fresh()->approved_by);

        // 2. Balance deducted
        $this->assertEquals(2.0, $balance->fresh()->used);
        $this->assertEquals(8.0, $balance->fresh()->remaining);

        // 3. Attendance marked
        $attMonday = Attendance::where('employee_id', $this->employee->id)->whereDate('date', $monday->format('Y-m-d'))->first();
        $this->assertNotNull($attMonday);
        $this->assertEquals(AttendanceStatusEnum::LEAVE, $attMonday->status);

        $attTuesday = Attendance::where('employee_id', $this->employee->id)->whereDate('date', $tuesday->format('Y-m-d'))->first();
        $this->assertNotNull($attTuesday);
        $this->assertEquals(AttendanceStatusEnum::LEAVE, $attTuesday->status);

        // 4. Notification sent
        Notification::assertSentTo($this->employee, LeaveStatusNotification::class);
    }

    public function test_manager_can_reject_leave_request(): void
    {
        Notification::fake();

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => '2026-10-05',
            'to_date' => '2026-10-06',
            'days' => 2.0,
            'half_day' => false,
            'reason' => 'Sprint time leave',
            'status' => LeaveRequestStatusEnum::PENDING,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('leaves.action', $leaveRequest), [
            'action' => 'reject',
            'remark' => 'Critical deadline this week',
        ]);

        $response->assertRedirect();
        $this->assertEquals(LeaveRequestStatusEnum::REJECTED, $leaveRequest->fresh()->status);
        $this->assertEquals('Critical deadline this week', $leaveRequest->fresh()->remark);

        Notification::assertSentTo($this->employee, LeaveStatusNotification::class);
    }

    public function test_employee_can_cancel_pending_leave_request(): void
    {
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'from_date' => '2026-10-05',
            'to_date' => '2026-10-06',
            'days' => 2.0,
            'half_day' => false,
            'reason' => 'Personal work',
            'status' => LeaveRequestStatusEnum::PENDING,
        ]);

        $response = $this->actingAs($this->employee)->delete(route('leaves.cancel', $leaveRequest));

        $response->assertRedirect();
        $this->assertEquals(LeaveRequestStatusEnum::CANCELLED, $leaveRequest->fresh()->status);
    }

    public function test_yearly_leave_allocation_artisan_command_with_carry_forward(): void
    {
        // Leave Type with carry forward enabled, max 5 days
        $annualType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'days_per_year' => 15,
            'is_paid' => true,
            'carry_forward' => true,
            'max_carry' => 5,
            'status' => StatusEnum::ACTIVE,
            'color' => '#10b981',
        ]);

        // Attach employee details
        $this->employee->employeeDetail()->create([
            'emp_code' => 'EMP-TEST-01',
            'status' => EmployeeStatusEnum::ACTIVE,
            'joining_date' => '2024-01-01',
        ]);

        // Year 2025: 15 allocated, 7 used -> 8 remaining
        LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $annualType->id,
            'year' => 2025,
            'allocated' => 15,
            'used' => 7,
            'carried' => 0,
        ]);

        // Run artisan command for year 2026
        $this->artisan('leave:allocate-yearly --year=2026')
            ->assertSuccessful();

        $balance2026 = LeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $annualType->id)
            ->where('year', 2026)
            ->first();

        $this->assertNotNull($balance2026);
        $this->assertEquals(15.0, $balance2026->allocated);
        // 8 remaining from 2025, capped at max_carry 5
        $this->assertEquals(5.0, $balance2026->carried);
        $this->assertEquals(20.0, $balance2026->total_available);
    }

    public function test_leave_types_crud(): void
    {
        // 1. Index
        $response = $this->actingAs($this->admin)->get(route('leave-types.index'));
        $response->assertStatus(200);

        // 2. Create
        $response = $this->actingAs($this->admin)->post(route('leave-types.store'), [
            'name' => 'Maternity Leave',
            'code' => 'ML',
            'days_per_year' => 90,
            'is_paid' => 1,
            'carry_forward' => 0,
            'status' => StatusEnum::ACTIVE->value,
            'color' => '#ec4899',
            'description' => 'Maternity leave for female employees.',
        ]);
        $response->assertRedirect(route('leave-types.index'));
        $this->assertDatabaseHas('leave_types', ['code' => 'ML', 'days_per_year' => 90]);

        $createdType = LeaveType::where('code', 'ML')->first();

        // 3. Update
        $response = $this->actingAs($this->admin)->put(route('leave-types.update', $createdType), [
            'name' => 'Maternity Leave Updated',
            'code' => 'ML',
            'days_per_year' => 120,
            'is_paid' => 1,
            'carry_forward' => 0,
            'status' => StatusEnum::ACTIVE->value,
            'color' => '#ec4899',
            'description' => 'Updated policy',
        ]);
        $response->assertRedirect(route('leave-types.index'));
        $this->assertEquals(120, $createdType->fresh()->days_per_year);

        // 4. Soft Delete
        $response = $this->actingAs($this->admin)->delete(route('leave-types.destroy', $createdType));
        $response->assertRedirect(route('leave-types.index'));
        $this->assertSoftDeleted('leave_types', ['id' => $createdType->id]);
    }

    public function test_unauthorized_user_cannot_manage_or_approve_leaves(): void
    {
        $otherUser = User::factory()->create(); // No roles

        $response = $this->actingAs($otherUser)->get(route('leave-types.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($otherUser)->get(route('leaves.requests'));
        $response->assertStatus(403);
    }
}
