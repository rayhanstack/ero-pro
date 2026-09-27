<?php

namespace Tests\Feature;

use App\Enums\EmploymentTypeEnum;
use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryStructureManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected Department $department;
    protected Designation $designation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->department = Department::create(['name' => 'Engineering', 'code' => 'DEP-001', 'status' => 'active']);
        $this->designation = Designation::create([
            'department_id' => $this->department->id,
            'name' => 'Senior Software Engineer',
            'status' => 'active',
        ]);

        $this->employee = User::factory()->create([
            'name' => 'Jane Developer',
            'email' => 'jane@erp.test',
        ]);
        $this->employee->assignRole('Employee');

        EmployeeDetail::create([
            'user_id' => $this->employee->id,
            'emp_code' => 'EMP-0010',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'employment_type' => EmploymentTypeEnum::FULL_TIME,
            'basic_salary' => 5000.00,
        ]);
    }

    public function test_authorized_user_can_view_salary_components(): void
    {
        SalaryComponent::create([
            'name' => 'House Rent Allowance (HRA)',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
            'value' => 40.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->get(route('payroll.components.index'));

        $response->assertStatus(200);
        $response->assertSee('House Rent Allowance (HRA)');
        $response->assertSee('40.00%');
    }

    public function test_authorized_user_can_create_salary_component(): void
    {
        $data = [
            'name' => 'Performance Bonus',
            'type' => SalaryComponentTypeEnum::EARNING->value,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED->value,
            'value' => 500.00,
            'is_taxable' => 1,
            'status' => SalaryComponentStatusEnum::ACTIVE->value,
            'description' => 'Monthly performance reward',
        ];

        $response = $this->actingAs($this->admin)->post(route('payroll.components.store'), $data);

        $response->assertRedirect(route('payroll.components.index'));
        $this->assertDatabaseHas('salary_components', [
            'name' => 'Performance Bonus',
            'type' => 'earning',
            'calc_type' => 'fixed',
            'value' => 500.00,
            'is_taxable' => 1,
        ]);
    }

    public function test_validation_errors_when_creating_component_with_invalid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('payroll.components.store'), [
            'name' => '',
            'type' => 'invalid_type',
            'value' => 'not_a_number',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'calc_type', 'value', 'status']);
    }

    public function test_authorized_user_can_edit_and_update_salary_component(): void
    {
        $component = SalaryComponent::create([
            'name' => 'Old Component Name',
            'type' => SalaryComponentTypeEnum::DEDUCTION,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 100.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $resEdit = $this->actingAs($this->admin)->get(route('payroll.components.edit', $component));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Old Component Name');

        $resUpdate = $this->actingAs($this->admin)->put(route('payroll.components.update', $component), [
            'name' => 'Updated Deduction Name',
            'type' => SalaryComponentTypeEnum::DEDUCTION->value,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC->value,
            'value' => 5.00,
            'is_taxable' => 0,
            'status' => SalaryComponentStatusEnum::ACTIVE->value,
        ]);

        $resUpdate->assertRedirect(route('payroll.components.index'));
        $component->refresh();
        $this->assertEquals('Updated Deduction Name', $component->name);
        $this->assertEquals(SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC, $component->calc_type);
        $this->assertEquals(5.00, (float) $component->value);
    }

    public function test_authorized_user_can_soft_delete_salary_component(): void
    {
        $component = SalaryComponent::create([
            'name' => 'Temporary Allowance',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 50.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('payroll.components.destroy', $component));

        $response->assertRedirect(route('payroll.components.index'));
        $this->assertSoftDeleted('salary_components', ['id' => $component->id]);
    }

    public function test_authorized_user_can_view_salary_structure_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('payroll.salary-structure.index'));

        $response->assertStatus(200);
        $response->assertSee('Jane Developer');
        $response->assertSee('EMP-0010');
        $response->assertSee('Senior Software Engineer');
        $response->assertSee('$5,000.00');
    }

    public function test_authorized_user_can_configure_employee_salary_structure(): void
    {
        $hra = SalaryComponent::create([
            'name' => 'HRA Allowance',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
            'value' => 40.00, // 40% of 6,000 = 2,400
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $med = SalaryComponent::create([
            'name' => 'Medical Fixed',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 200.00, // Fixed 200
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $pf = SalaryComponent::create([
            'name' => 'Provident Fund',
            'type' => SalaryComponentTypeEnum::DEDUCTION,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
            'value' => 10.00, // 10% of 6,000 = 600
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        // Edit structure page
        $resEdit = $this->actingAs($this->admin)->get(route('payroll.salary-structure.edit', $this->employee));
        $resEdit->assertStatus(200);
        $resEdit->assertSee('Jane Developer');
        $resEdit->assertSee('HRA Allowance');
        $resEdit->assertSee('Medical Fixed');

        // Update structure with new basic (6000), enable HRA with custom override (50%), enable PF default (10%)
        $updateData = [
            'basic_salary' => 6000.00,
            'components' => [
                $hra->id => [
                    'enabled' => 1,
                    'value' => 50.00, // override from 40% to 50%
                ],
                $pf->id => [
                    'enabled' => 1,
                    'value' => null, // use default 10%
                ],
            ],
        ];

        $resUpdate = $this->actingAs($this->admin)->put(route('payroll.salary-structure.update', $this->employee), $updateData);
        $resUpdate->assertRedirect(route('payroll.salary-structure.index'));

        $this->employee->refresh();
        $this->assertEquals(6000.00, (float) $this->employee->employeeDetail->basic_salary);

        $this->assertDatabaseHas('employee_salary_components', [
            'employee_id' => $this->employee->id,
            'component_id' => $hra->id,
            'value' => 50.00,
        ]);

        $this->assertDatabaseHas('employee_salary_components', [
            'employee_id' => $this->employee->id,
            'component_id' => $pf->id,
            'value' => null,
        ]);

        // Medical was not enabled, should not exist in mapping
        $this->assertDatabaseMissing('employee_salary_components', [
            'employee_id' => $this->employee->id,
            'component_id' => $med->id,
        ]);
    }

    public function test_calculate_preview_endpoint_returns_json_breakdown(): void
    {
        $hra = SalaryComponent::create([
            'name' => 'HRA Component',
            'type' => SalaryComponentTypeEnum::EARNING,
            'calc_type' => SalaryComponentCalcTypeEnum::PERCENT_OF_BASIC,
            'value' => 40.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $tax = SalaryComponent::create([
            'name' => 'Tax Deduction',
            'type' => SalaryComponentTypeEnum::DEDUCTION,
            'calc_type' => SalaryComponentCalcTypeEnum::FIXED,
            'value' => 150.00,
            'status' => SalaryComponentStatusEnum::ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('payroll.salary-structure.calculate-preview'), [
            'basic_salary' => 5000.00,
            'components' => [
                $hra->id => ['enabled' => 1, 'value' => 40.00],
                $tax->id => ['enabled' => 1, 'value' => 150.00],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'basic_salary' => 5000.00,
            'total_earnings' => 2000.00, // 40% of 5000
            'total_deductions' => 150.00,
            'gross_salary' => 7000.00, // 5000 + 2000
            'net_salary' => 6850.00, // 7000 - 150
        ]);
    }

    public function test_unauthorized_user_cannot_access_payroll_routes(): void
    {
        $unauthorized = User::factory()->create();

        $res1 = $this->actingAs($unauthorized)->get(route('payroll.components.index'));
        $res1->assertStatus(403);

        $res2 = $this->actingAs($unauthorized)->get(route('payroll.salary-structure.index'));
        $res2->assertStatus(403);
    }
}
