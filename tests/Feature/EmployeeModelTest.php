<?php

namespace Tests\Feature;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ShiftSeeder::class,
        ]);
    }

    public function test_employee_generates_emp_code_automatically_on_creation(): void
    {
        $dept = Department::first();
        $desig = Designation::where('department_id', $dept->id)->first();
        $shift = Shift::first();

        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
            'joining_date' => '2023-01-01',
            'gender' => GenderEnum::MALE,
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);

        $this->assertStringStartsWith('EMP-', $employee->emp_code);
        $this->assertEquals('John Doe', $employee->full_name);
        $this->assertEquals('John Doe', $employee->name);
    }

    public function test_employee_relationships_function_correctly(): void
    {
        $user = User::factory()->create();
        $dept = Department::first();
        $desig = Designation::where('department_id', $dept->id)->first();
        $shift = Shift::first();

        $manager = Employee::factory()->create([
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
        ]);

        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
            'manager_id' => $manager->id,
        ]);

        $bank = EmployeeBankAccount::factory()->create([
            'employee_id' => $employee->id,
            'is_primary' => true,
        ]);

        $contact = EmployeeEmergencyContact::factory()->create([
            'employee_id' => $employee->id,
        ]);

        $doc = EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
        ]);

        // Test Employee Relations
        $this->assertEquals($user->id, $employee->user->id);
        $this->assertEquals($dept->id, $employee->department->id);
        $this->assertEquals($desig->id, $employee->designation->id);
        $this->assertEquals($shift->id, $employee->shift->id);
        $this->assertEquals($manager->id, $employee->manager->id);
        $this->assertCount(1, $employee->bankAccounts);
        $this->assertEquals($bank->id, $employee->primaryBankAccount->id);
        $this->assertCount(1, $employee->emergencyContacts);
        $this->assertCount(1, $employee->documents);

        // Test Inverse Relations
        $this->assertCount(1, $manager->subordinates);
        $this->assertEquals($employee->id, $user->fresh()->employee->id);
        $this->assertTrue($dept->employees->contains($employee));
        $this->assertTrue($desig->employees->contains($employee));
        $this->assertTrue($shift->employees->contains($employee));
    }

    public function test_department_head_relation_functions_correctly(): void
    {
        $dept = Department::first();
        $desig = Designation::where('department_id', $dept->id)->first();
        $shift = Shift::first();

        $employee = Employee::factory()->create([
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
        ]);

        $dept->update(['head_id' => $employee->id]);

        $this->assertEquals($employee->id, $dept->fresh()->head->id);
    }

    public function test_employee_scopes_filter_records_correctly(): void
    {
        $dept1 = Department::where('code', 'ENG')->first();
        $dept2 = Department::where('code', 'HR')->first();
        $desig1 = Designation::where('department_id', $dept1->id)->first();
        $desig2 = Designation::where('department_id', $dept2->id)->first();
        $shift = Shift::first();

        $activeEmp = Employee::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
            'email' => 'alice@example.com',
            'department_id' => $dept1->id,
            'designation_id' => $desig1->id,
            'shift_id' => $shift->id,
            'status' => EmployeeStatusEnum::ACTIVE,
            'employment_type' => EmploymentTypeEnum::FULL_TIME,
        ]);

        $inactiveEmp = Employee::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'email' => 'bob@example.com',
            'department_id' => $dept2->id,
            'designation_id' => $desig2->id,
            'shift_id' => $shift->id,
            'status' => EmployeeStatusEnum::RESIGNED,
            'employment_type' => EmploymentTypeEnum::PART_TIME,
        ]);

        $this->assertTrue(Employee::active()->pluck('id')->contains($activeEmp->id));
        $this->assertFalse(Employee::active()->pluck('id')->contains($inactiveEmp->id));
        $this->assertTrue(Employee::search('Wonderland')->pluck('id')->contains($activeEmp->id));
        $this->assertTrue(Employee::filterByDepartment($dept1->id)->pluck('id')->contains($activeEmp->id));
        $this->assertFalse(Employee::filterByDepartment($dept1->id)->pluck('id')->contains($inactiveEmp->id));
        $this->assertTrue(Employee::filterByStatus(EmployeeStatusEnum::RESIGNED)->pluck('id')->contains($inactiveEmp->id));
        $this->assertTrue(Employee::filterByEmploymentType(EmploymentTypeEnum::PART_TIME)->pluck('id')->contains($inactiveEmp->id));
    }

    public function test_employee_soft_delete_works_as_expected(): void
    {
        $employee = Employee::factory()->create();

        $employee->delete();

        $this->assertNull(Employee::find($employee->id));
        $this->assertNotNull(Employee::withTrashed()->find($employee->id));
    }
}
