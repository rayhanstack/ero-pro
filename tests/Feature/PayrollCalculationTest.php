<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\LeaveRequestStatusEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Enums\PayslipStatusEnum;
use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Events\PayslipPaid;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryComponent;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\User;
use App\Models\Weekend;
use App\Services\Payroll\PayrollService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee1;
    protected User $employee2;
    protected Department $department;
    protected Designation $designation;
    protected Shift $shift;
    protected PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(\Database\Seeders\WeekendSeeder::class);

        $this->payrollService = app(PayrollService::class);

        $this->admin = User::factory()->create(['name' => 'Admin User', 'email' => 'admin@erp.test']);
        $this->admin->assignRole('Super Admin');

        $this->department = Department::create(['name' => 'Tech', 'code' => 'TECH', 'status' => 'active']);
        $this->designation = Designation::create([
            'department_id' => $this->department->id,
            'name' => 'Software Engineer',
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'name' => 'Regular Shift',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'status' => 'active',
        ]);

        // Employee 1: Basic salary = 60000
        $this->employee1 = User::factory()->create(['name' => 'Alice Worker', 'email' => 'alice@erp.test']);
        $this->employee1->assignRole('Employee');
        EmployeeDetail::create([
            'user_id' => $this->employee1->id,
            'emp_code' => 'EMP-001',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'employment_type' => EmploymentTypeEnum::FULL_TIME,
            'basic_salary' => 60000.00,
        ]);

        // Employee 2: Basic salary = 90000
        $this->employee2 = User::factory()->create(['name' => 'Bob Engineer', 'email' => 'bob@erp.test']);
        $this->employee2->assignRole('Employee');
        EmployeeDetail::create([
            'user_id' => $this->employee2->id,
            'emp_code' => 'EMP-002',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'employment_type' => EmploymentTypeEnum::FULL_TIME,
            'basic_salary' => 90000.00,
        ]);
    }

    public function test_can_create_and_list_payroll_periods(): void
    {
        $response = $this->actingAs($this->admin)->post(route('payroll.periods.store'), [
            'month' => 9,
            'year' => 2026,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_periods', [
            'month' => 9,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT->value,
        ]);

        $listResponse = $this->actingAs($this->admin)->get(route('payroll.periods.index'));
        $listResponse->assertOk();
        $listResponse->assertSee('September 2026');
    }

    public function test_cannot_create_duplicate_payroll_period_for_same_month_and_year(): void
    {
        PayrollPeriod::create([
            'month' => 5,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $response = $this->actingAs($this->admin)->post(route('payroll.periods.store'), [
            'month' => 5,
            'year' => 2026,
        ]);

        $response->assertSessionHasErrors(['month']);
    }

    public function test_payroll_service_generates_payslips_with_components_attendance_leaves_and_overtime(): void
    {
        // Setup Components
        // 1. Fixed Earning: Medical = 2000
        $medical = SalaryComponent::create([
            'name' => 'Medical Allowance',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 2000.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        // 2. Percentage Earning: HRA = 20% of basic
        $hra = SalaryComponent::create([
            'name' => 'House Rent Allowance',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
            'value' => 20.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        // 3. Fixed Deduction: Provident Fund = 1500
        $pf = SalaryComponent::create([
            'name' => 'Provident Fund',
            'type' => SalaryComponentTypeEnum::DEDUCTION,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 1500.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        // Assign Medical to Alice: default 2000
        EmployeeSalaryComponent::create([
            'employee_id' => $this->employee1->id,
            'component_id' => $medical->id,
            'value' => null,
        ]);

        // Override HRA for Alice: 25% instead of 20%
        EmployeeSalaryComponent::create([
            'employee_id' => $this->employee1->id,
            'component_id' => $hra->id,
            'value' => 25.00,
        ]);

        // Assign PF to Alice: default 1500
        EmployeeSalaryComponent::create([
            'employee_id' => $this->employee1->id,
            'component_id' => $pf->id,
            'value' => null,
        ]);

        // Paid Leave Type
        $paidLeaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'days_per_year' => 14,
            'is_paid' => true,
            'status' => 'active',
        ]);

        // Unpaid Leave Type
        $unpaidLeaveType = LeaveType::create([
            'name' => 'Loss of Pay',
            'code' => 'LOP',
            'days_per_year' => 0,
            'is_paid' => false,
            'status' => 'active',
        ]);

        // Create Period for September 2026
        $period = PayrollPeriod::create([
            'month' => 9,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        // Setup Attendance for Alice in Sept 2026 (e.g. 5 days present with 120 min overtime on one day)
        Attendance::create([
            'employee_id' => $this->employee1->id,
            'date' => '2026-09-01',
            'status' => AttendanceStatusEnum::PRESENT,
            'overtime_minutes' => 120, // 2 hours
        ]);
        Attendance::create([
            'employee_id' => $this->employee1->id,
            'date' => '2026-09-02',
            'status' => AttendanceStatusEnum::PRESENT,
            'overtime_minutes' => 0,
        ]);

        // Alice takes 1 day Paid Leave
        LeaveRequest::create([
            'employee_id' => $this->employee1->id,
            'leave_type_id' => $paidLeaveType->id,
            'from_date' => '2026-09-03',
            'to_date' => '2026-09-03',
            'days' => 1,
            'reason' => 'Casual personal leave',
            'status' => LeaveRequestStatusEnum::APPROVED,
        ]);

        // Alice takes 1 day Unpaid Leave
        LeaveRequest::create([
            'employee_id' => $this->employee1->id,
            'leave_type_id' => $unpaidLeaveType->id,
            'from_date' => '2026-09-04',
            'to_date' => '2026-09-04',
            'days' => 1,
            'reason' => 'Emergency personal leave without pay',
            'status' => LeaveRequestStatusEnum::APPROVED,
        ]);

        // Generate Payroll
        $count = $this->payrollService->generate($period);
        $this->assertEquals(2, $count);

        $period->refresh();
        $this->assertEquals(PayrollPeriodStatusEnum::PROCESSED, $period->status);
        $this->assertEquals(2, $period->total_employees);

        // Verify Alice's payslip
        $alicePayslip = Payslip::where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employee1->id)
            ->first();

        $this->assertNotNull($alicePayslip);
        $this->assertEquals(60000.00, $alicePayslip->basic);
        $this->assertEquals(AttendanceStatusEnum::PRESENT->value ? 2 : 2, $alicePayslip->present_days);
        $this->assertEquals(2, $alicePayslip->leave_days);
        $this->assertGreaterThan(0, $alicePayslip->absent_days);

        // Total earnings: Medical (2000) + HRA (25% of 60000 = 15000) = 17000
        $this->assertEquals(17000.00, $alicePayslip->total_earnings);

        // Total deductions: PF (1500)
        $this->assertEquals(1500.00, $alicePayslip->total_deductions);

        // Overtime amount: 120 mins = 2 hours. Per day = 60000 / working_days.
        // Hourly rate = per_day / 8. Overtime rate = 1.0 (or setting default).
        $this->assertGreaterThan(0, $alicePayslip->overtime_amount);

        // Absent deduction: unpaid leave + any days not attended/weekend/holiday
        $this->assertGreaterThan(0, $alicePayslip->absent_deduction);

        // Net pay formula check
        $expectedNet = round(
            $alicePayslip->basic +
            $alicePayslip->total_earnings +
            $alicePayslip->overtime_amount +
            $alicePayslip->bonus -
            $alicePayslip->total_deductions -
            $alicePayslip->absent_deduction -
            $alicePayslip->tax,
            2
        );
        $this->assertEquals($expectedNet, $alicePayslip->net_pay);

        // Check Payslip Items were recorded
        $this->assertDatabaseHas('payslip_items', [
            'payslip_id' => $alicePayslip->id,
            'name' => 'Medical Allowance',
            'amount' => 2000.00,
        ]);
        $this->assertDatabaseHas('payslip_items', [
            'payslip_id' => $alicePayslip->id,
            'name' => 'House Rent Allowance',
            'amount' => 15000.00,
        ]);
    }

    public function test_regeneration_is_idempotent_for_draft_or_processed_periods(): void
    {
        $period = PayrollPeriod::create([
            'month' => 10,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        // First generation
        $this->payrollService->generate($period);
        $firstCount = Payslip::where('payroll_period_id', $period->id)->count();
        $this->assertEquals(2, $firstCount);

        // Regenerate
        $this->payrollService->generate($period);
        $secondCount = Payslip::where('payroll_period_id', $period->id)->count();
        $this->assertEquals(2, $secondCount);
    }

    public function test_can_update_payslip_adjustments(): void
    {
        $period = PayrollPeriod::create([
            'month' => 11,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);

        $payslip = Payslip::where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employee1->id)
            ->first();

        $initialNet = $payslip->net_pay;

        $response = $this->actingAs($this->admin)->put(route('payroll.payslips.update', $payslip), [
            'bonus' => 5000.00,
            'absent_deduction' => 0.00,
            'tax' => 1000.00,
            'note' => 'Performance award added',
        ]);

        $response->assertRedirect();
        $payslip->refresh();

        $this->assertEquals(5000.00, $payslip->bonus);
        $this->assertEquals(0.00, $payslip->absent_deduction);
        $this->assertEquals(1000.00, $payslip->tax);
        $this->assertEquals('Performance award added', $payslip->note);
    }

    public function test_approving_and_marking_paid_dispatches_payslip_paid_event(): void
    {
        Event::fake([PayslipPaid::class]);

        $period = PayrollPeriod::create([
            'month' => 12,
            'year' => 2026,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);

        $payslip = Payslip::where('payroll_period_id', $period->id)->first();

        // 1. Approve
        $approveRes = $this->actingAs($this->admin)->post(route('payroll.payslips.approve', $payslip));
        $approveRes->assertRedirect();
        $payslip->refresh();
        $this->assertEquals(PayslipStatusEnum::APPROVED, $payslip->status);

        // 2. Mark Paid
        $paidRes = $this->actingAs($this->admin)->post(route('payroll.payslips.mark-paid', $payslip));
        $paidRes->assertRedirect();
        $payslip->refresh();
        $this->assertEquals(PayslipStatusEnum::PAID, $payslip->status);
        $this->assertNotNull($payslip->paid_at);

        // Assert Event Dispatched
        Event::assertDispatched(PayslipPaid::class, function ($event) use ($payslip) {
            return $event->payslip->id === $payslip->id;
        });
    }

    public function test_bulk_approve_and_bulk_mark_paid(): void
    {
        Event::fake([PayslipPaid::class]);

        $period = PayrollPeriod::create([
            'month' => 1,
            'year' => 2027,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);

        // Bulk Approve
        $this->actingAs($this->admin)->post(route('payroll.periods.bulk-approve', $period));
        $this->assertEquals(2, Payslip::where('payroll_period_id', $period->id)->where('status', PayslipStatusEnum::APPROVED)->count());

        // Bulk Mark Paid
        $this->actingAs($this->admin)->post(route('payroll.periods.bulk-mark-paid', $period));
        $this->assertEquals(2, Payslip::where('payroll_period_id', $period->id)->where('status', PayslipStatusEnum::PAID)->count());

        $period->refresh();
        $this->assertEquals(PayrollPeriodStatusEnum::PAID, $period->status);

        // Event should be dispatched for both payslips
        Event::assertDispatched(PayslipPaid::class, 2);
    }

    public function test_locked_period_cannot_be_regenerated_or_deleted(): void
    {
        $period = PayrollPeriod::create([
            'month' => 2,
            'year' => 2027,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);

        // Lock period
        $lockRes = $this->actingAs($this->admin)->post(route('payroll.periods.lock', $period));
        $lockRes->assertRedirect();

        $period->refresh();
        $this->assertEquals(PayrollPeriodStatusEnum::LOCKED, $period->status);

        // Attempting to regenerate should be blocked
        $regenRes = $this->actingAs($this->admin)->post(route('payroll.periods.generate', $period));
        $regenRes->assertSessionHas('error');

        // Attempting to delete should be blocked
        $delRes = $this->actingAs($this->admin)->delete(route('payroll.periods.destroy', $period));
        $delRes->assertSessionHas('error');
    }

    public function test_can_view_payslip_detail_and_download_pdf(): void
    {
        $period = PayrollPeriod::create([
            'month' => 3,
            'year' => 2027,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);
        $payslip = Payslip::where('payroll_period_id', $period->id)->first();

        // View detail
        $viewRes = $this->actingAs($this->admin)->get(route('payroll.payslips.show', $payslip));
        $viewRes->assertOk();
        $viewRes->assertSee($payslip->employee->name);
        $viewRes->assertSee('Earnings & Allowances');

        // PDF download
        $pdfRes = $this->actingAs($this->admin)->get(route('payroll.payslips.pdf', $payslip));
        $pdfRes->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfRes->headers->get('Content-Type') ?? '');
    }

    public function test_employee_can_view_their_own_payslips_page(): void
    {
        $period = PayrollPeriod::create([
            'month' => 4,
            'year' => 2027,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $this->payrollService->generate($period);

        // Approve Alice's payslip
        $payslip = Payslip::where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employee1->id)
            ->first();
        $this->payrollService->approvePayslip($payslip);

        // Alice views My Payslips
        $response = $this->actingAs($this->employee1)->get(route('payroll.my-payslips'));
        $response->assertOk();
        $response->assertSee('My Payslips');
        $response->assertSee('April 2027');
    }

    public function test_unauthorized_user_cannot_access_payroll_management(): void
    {
        $guestUser = User::factory()->create(); // No roles or permissions

        $period = PayrollPeriod::create([
            'month' => 6,
            'year' => 2027,
            'status' => PayrollPeriodStatusEnum::DRAFT,
        ]);

        $response = $this->actingAs($guestUser)->get(route('payroll.periods.index'));
        $response->assertForbidden();

        $response2 = $this->actingAs($guestUser)->post(route('payroll.periods.generate', $period));
        $response2->assertForbidden();
    }
}
