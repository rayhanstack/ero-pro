<?php

namespace Tests\Feature;

use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDetail;
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

    public function test_employee_detail_generates_emp_code_automatically_on_creation(): void
    {
        $dept = Department::first();
        $desig = Designation::where('department_id', $dept->id)->first();
        $shift = Shift::first();
        $user = User::factory()->create(['name' => 'John Doe']);

        $detail = EmployeeDetail::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
            'joining_date' => '2023-01-01',
            'gender' => GenderEnum::MALE,
        ]);

        $this->assertStringStartsWith('EMP-', $detail->emp_code);
        $this->assertEquals($detail->emp_code, $user->emp_code);
        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_employee_relationships_function_correctly(): void
    {
        $dept = Department::first();
        $desig = Designation::where('department_id', $dept->id)->first();
        $shift = Shift::first();

        $manager = User::factory()->create(['name' => 'Manager User']);
        $employee = User::factory()->create(['name' => 'Staff User']);

        $detail = EmployeeDetail::factory()->create([
            'user_id' => $employee->id,
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'shift_id' => $shift->id,
            'manager_id' => $manager->id,
        ]);

        $bank = EmployeeBankAccount::factory()->create([
            'user_id' => $employee->id,
            'is_primary' => true,
        ]);

        $contact = EmployeeEmergencyContact::factory()->create([
            'user_id' => $employee->id,
        ]);

        $doc = EmployeeDocument::factory()->create([
            'user_id' => $employee->id,
        ]);

        // Test User / Employee Relations
        $this->assertEquals($detail->id, $employee->detail->id);
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
        $this->assertTrue($dept->employeeDetails->contains($detail));
        $this->assertTrue($desig->employeeDetails->contains($detail));
        $this->assertTrue($shift->employeeDetails->contains($detail));
    }

    public function test_department_head_relation_functions_correctly(): void
    {
        $dept = Department::first();
        $user = User::factory()->create();

        $dept->update(['head_id' => $user->id]);

        $this->assertEquals($user->id, $dept->fresh()->head->id);
    }

    public function test_user_scopes_filter_records_correctly(): void
    {
        $dept1 = Department::where('code', 'ENG')->first();
        $dept2 = Department::where('code', 'HR')->first();
        $desig1 = Designation::where('department_id', $dept1->id)->first();
        $desig2 = Designation::where('department_id', $dept2->id)->first();
        $shift = Shift::first();

        $activeUser = User::factory()->create([
            'name' => 'Alice Wonderland',
            'email' => 'alice@example.com',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);
        $activeUser->assignRole('Employee');

        EmployeeDetail::factory()->create([
            'user_id' => $activeUser->id,
            'department_id' => $dept1->id,
            'designation_id' => $desig1->id,
            'shift_id' => $shift->id,
            'employment_type' => EmploymentTypeEnum::FULL_TIME,
        ]);

        $inactiveUser = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
            'status' => EmployeeStatusEnum::RESIGNED,
        ]);
        $inactiveUser->assignRole('Employee');

        EmployeeDetail::factory()->create([
            'user_id' => $inactiveUser->id,
            'department_id' => $dept2->id,
            'designation_id' => $desig2->id,
            'shift_id' => $shift->id,
            'employment_type' => EmploymentTypeEnum::PART_TIME,
        ]);

        $this->assertTrue(User::active()->pluck('id')->contains($activeUser->id));
        $this->assertFalse(User::active()->pluck('id')->contains($inactiveUser->id));
        $this->assertTrue(User::search('Wonderland')->pluck('id')->contains($activeUser->id));
        $this->assertTrue(User::filterByDepartment($dept1->id)->pluck('id')->contains($activeUser->id));
        $this->assertFalse(User::filterByDepartment($dept1->id)->pluck('id')->contains($inactiveUser->id));
        $this->assertTrue(User::filterByStatus(EmployeeStatusEnum::RESIGNED)->pluck('id')->contains($inactiveUser->id));
        $this->assertTrue(User::filterByRole('Employee')->pluck('id')->contains($activeUser->id));
    }

    public function test_user_soft_delete_works_as_expected(): void
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }
}
