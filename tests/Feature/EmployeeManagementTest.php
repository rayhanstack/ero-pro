<?php

namespace Tests\Feature;

use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDocument;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularUser;
    protected Department $department;
    protected Designation $designation;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ShiftSeeder::class,
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->regularUser = User::factory()->create();

        $this->department = Department::first();
        $this->designation = Designation::where('department_id', $this->department->id)->first();
        $this->shift = Shift::first();
    }

    public function test_employees_index_page_can_be_rendered_in_table_and_grid_views(): void
    {
        Employee::factory()->count(5)->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        // Table view
        $response = $this->actingAs($this->superAdmin)->get(route('employees.index', ['view' => 'table']));
        $response->assertStatus(200);
        $response->assertSee(_trans('common.Employee Management'));
        $response->assertSee(_trans('common.Total Employees'));

        // Grid view
        $gridResponse = $this->actingAs($this->superAdmin)->get(route('employees.index', ['view' => 'grid']));
        $gridResponse->assertStatus(200);
        $gridResponse->assertSee(_trans('common.Employee Management'));
    }

    public function test_employees_index_filters_by_search_query_and_department(): void
    {
        $emp1 = Employee::factory()->create([
            'first_name' => 'Mahmudul',
            'last_name' => 'Hasan',
            'email' => 'mahmud.unique@erp.test',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $emp2 = Employee::factory()->create([
            'first_name' => 'Farhana',
            'last_name' => 'Akter',
            'email' => 'farhana.unique@erp.test',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('employees.index', ['search' => 'Mahmudul']));
        $response->assertStatus(200);
        $response->assertSee('Mahmudul Hasan');
        $response->assertDontSee('Farhana Akter');
    }

    public function test_employee_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('employees.create'));
        $response->assertStatus(200);
        $response->assertSee(_trans('common.Add New Employee'));
        $response->assertSee(_trans('common.Personal Details'));
    }

    public function test_employee_can_be_created_with_user_account_and_bank(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $doc = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->superAdmin)->post(route('employees.store'), [
            'first_name' => 'Kabir',
            'last_name' => 'Chowdhury',
            'email' => 'kabir.chowdhury@erp.test',
            'phone' => '+8801700112233',
            'dob' => '1992-05-15',
            'gender' => GenderEnum::MALE->value,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'joining_date' => '2023-01-10',
            'employment_type' => EmploymentTypeEnum::FULL_TIME->value,
            'status' => EmployeeStatusEnum::ACTIVE->value,
            'basic_salary' => 85000,
            'avatar' => $avatar,

            // User account
            'create_user_account' => 1,
            'role' => 'Employee',
            'user_password' => 'SecurePass123!',

            // Bank
            'bank' => 'City Bank PLC',
            'branch' => 'Gulshan',
            'account_name' => 'Kabir Chowdhury',
            'account_no' => '11029384756',

            // Emergency
            'emergency_name' => 'Salma Chowdhury',
            'emergency_relationship' => 'Spouse',
            'emergency_phone' => '+8801811223344',

            // Document
            'document_title' => 'Employment Contract',
            'document_file' => $doc,
        ]);

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Kabir',
            'last_name' => 'Chowdhury',
            'email' => 'kabir.chowdhury@erp.test',
            'basic_salary' => 85000,
        ]);

        $employee = Employee::where('email', 'kabir.chowdhury@erp.test')->first();
        $this->assertNotNull($employee);
        $this->assertStringStartsWith('EMP-', $employee->emp_code);

        // Check user account created and linked
        $this->assertNotNull($employee->user_id);
        $this->assertDatabaseHas('users', [
            'email' => 'kabir.chowdhury@erp.test',
            'employee_id' => $employee->id,
        ]);

        // Check bank account created
        $this->assertDatabaseHas('employee_bank_accounts', [
            'employee_id' => $employee->id,
            'bank' => 'City Bank PLC',
            'account_no' => '11029384756',
        ]);

        // Check emergency contact created
        $this->assertDatabaseHas('employee_emergency_contacts', [
            'employee_id' => $employee->id,
            'name' => 'Salma Chowdhury',
        ]);

        // Check document created
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'title' => 'Employment Contract',
        ]);

        $response->assertRedirect(route('employees.show', $employee));
    }

    public function test_employee_creation_fails_on_validation_errors(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('employees.store'), [
            'first_name' => '',
            'email' => 'invalid-email',
            'department_id' => 9999,
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'department_id', 'designation_id', 'gender', 'joining_date', 'employment_type', 'status']);
    }

    public function test_employee_show_page_can_be_rendered(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('employees.show', $employee));
        $response->assertStatus(200);
        $response->assertSee($employee->full_name);
        $response->assertSee($employee->emp_code);
        $response->assertSee(_trans('common.Personal Details'));
    }

    public function test_employee_edit_page_can_be_rendered(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('employees.edit', $employee));
        $response->assertStatus(200);
        $response->assertSee($employee->first_name);
        $response->assertSee($employee->email);
    }

    public function test_employee_can_be_updated(): void
    {
        $employee = Employee::factory()->create([
            'first_name' => 'Original',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('employees.update', $employee), [
            'first_name' => 'UpdatedName',
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'gender' => GenderEnum::FEMALE->value,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'joining_date' => '2023-05-01',
            'employment_type' => EmploymentTypeEnum::CONTRACT->value,
            'status' => EmployeeStatusEnum::ON_LEAVE->value,
            'basic_salary' => 90000,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'UpdatedName',
            'gender' => GenderEnum::FEMALE->value,
            'employment_type' => EmploymentTypeEnum::CONTRACT->value,
            'status' => EmployeeStatusEnum::ON_LEAVE->value,
            'basic_salary' => 90000,
        ]);

        $response->assertRedirect(route('employees.show', $employee));
    }

    public function test_employee_status_can_be_changed(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch(route('employees.status', $employee), [
            'status' => EmployeeStatusEnum::TERMINATED->value,
        ]);

        $response->assertRedirect();
        $this->assertEquals(EmployeeStatusEnum::TERMINATED, $employee->fresh()->status);
    }

    public function test_employee_can_be_soft_deleted_and_restored(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        // Delete
        $response = $this->actingAs($this->superAdmin)->delete(route('employees.destroy', $employee));
        $response->assertRedirect(route('employees.index'));
        $this->assertSoftDeleted('employees', ['id' => $employee->id]);

        // Restore
        $restoreResponse = $this->actingAs($this->superAdmin)->post(route('employees.restore', $employee->id));
        $restoreResponse->assertRedirect(route('employees.index'));
        $this->assertNotSoftDeleted('employees', ['id' => $employee->id]);
    }

    public function test_employee_document_can_be_uploaded_and_deleted(): void
    {
        Storage::fake('public');

        $employee = Employee::factory()->create([
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->superAdmin)->post(route('employees.documents.store', $employee), [
            'title' => 'Degree Certificate',
            'file' => $file,
            'expiry_date' => '2028-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'title' => 'Degree Certificate',
        ]);

        $document = EmployeeDocument::where('title', 'Degree Certificate')->first();

        // Delete document
        $delResponse = $this->actingAs($this->superAdmin)->delete(route('employees.documents.destroy', $document));
        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('employee_documents', ['id' => $document->id]);
    }

    public function test_unauthorized_user_cannot_access_employees(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('employees.index'));
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->regularUser)->get(route('employees.create'));
        $createResponse->assertStatus(403);
    }
}
