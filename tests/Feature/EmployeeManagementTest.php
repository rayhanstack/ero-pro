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
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DesignationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ShiftSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create();
            $user->assignRole('Employee');
            EmployeeDetail::factory()->create([
                'user_id' => $user->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
            ]);
        }

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
        $user1 = User::factory()->create(['name' => 'Mahmudul Hasan', 'email' => 'mahmud.unique@erp.test']);
        $user1->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user1->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $user2 = User::factory()->create(['name' => 'Farhana Akter', 'email' => 'farhana.unique@erp.test']);
        $user2->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user2->id,
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
        $response->assertSee(_trans('common.Account & Personal'));
    }

    public function test_employee_can_be_created_with_unified_user_and_detail_records(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $doc = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->superAdmin)->post(route('employees.store'), [
            // Account & Personal
            'first_name' => 'Kabir',
            'last_name' => 'Chowdhury',
            'email' => 'kabir.chowdhury@erp.test',
            'phone' => '+8801700112233',
            'role' => 'Employee',
            'time_zone' => 'Asia/Dhaka',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'avatar' => $avatar,
            'dob' => '1992-05-15',
            'gender' => GenderEnum::MALE->value,
            'status' => EmployeeStatusEnum::ACTIVE->value,

            // Job
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'joining_date' => '2023-01-10',
            'employment_type' => EmploymentTypeEnum::FULL_TIME->value,
            'basic_salary' => 85000,

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

        $this->assertDatabaseHas('users', [
            'name' => 'Kabir Chowdhury',
            'email' => 'kabir.chowdhury@erp.test',
            'phone' => '+8801700112233',
        ]);

        $user = User::where('email', 'kabir.chowdhury@erp.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Employee'));

        // Check employee details created
        $this->assertDatabaseHas('employee_details', [
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'basic_salary' => 85000,
        ]);

        $this->assertStringStartsWith('EMP-', $user->emp_code);

        // Check bank account created
        $this->assertDatabaseHas('employee_bank_accounts', [
            'user_id' => $user->id,
            'bank' => 'City Bank PLC',
            'account_no' => '11029384756',
        ]);

        // Check emergency contact created
        $this->assertDatabaseHas('employee_emergency_contacts', [
            'user_id' => $user->id,
            'name' => 'Salma Chowdhury',
        ]);

        // Check document created
        $this->assertDatabaseHas('employee_documents', [
            'user_id' => $user->id,
            'title' => 'Employment Contract',
        ]);

        $response->assertRedirect(route('employees.show', $user));
    }

    public function test_employee_creation_fails_on_validation_errors(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('employees.store'), [
            'first_name' => '',
            'email' => 'invalid-email',
            'department_id' => 9999,
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'role', 'password', 'department_id', 'gender']);
    }

    public function test_employee_creation_step1_stores_and_redirects_to_edit_step2(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('employees.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe.step@example.com',
            'phone' => '01711999888',
            'gender' => GenderEnum::MALE->value,
            'role' => 'Employee',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'next_step' => 'job',
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'john.doe.step@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->detail);
        $response->assertRedirect(route('employees.edit', ['employee' => $user->id, 'step' => 'job']));

        // Step 2 update
        $updateResponse = $this->actingAs($this->superAdmin)->put(route('employees.update', $user), [
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'employment_type' => EmploymentTypeEnum::FULL_TIME->value,
            'joining_date' => '2025-01-15',
            'next_step' => 'salary',
        ]);

        $updateResponse->assertRedirect(route('employees.edit', ['employee' => $user->id, 'step' => 'salary']));
        $this->assertDatabaseHas('employee_details', [
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
        ]);
    }

    public function test_employee_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create(['name' => 'Test Employee']);
        $user->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('employees.show', $user));
        $response->assertStatus(200);
        $response->assertSee('Test Employee');
        $response->assertSee($user->emp_code);
        $response->assertSee(_trans('common.Personal Details'));
    }

    public function test_employee_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create(['name' => 'Test Employee', 'email' => 'edit.test@erp.test']);
        $user->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('employees.edit', $user));
        $response->assertStatus(200);
        $response->assertSee('Test');
        $response->assertSee('edit.test@erp.test');
    }

    public function test_employee_can_be_updated(): void
    {
        $user = User::factory()->create(['name' => 'Original Name', 'email' => 'original@erp.test']);
        $user->assignRole('Employee');
        $detail = EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('employees.update', $user), [
            'first_name' => 'UpdatedName',
            'last_name' => 'UpdatedLast',
            'email' => 'original@erp.test',
            'role' => 'Employee',
            'gender' => GenderEnum::FEMALE->value,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'joining_date' => '2023-05-01',
            'employment_type' => EmploymentTypeEnum::CONTRACT->value,
            'status' => EmployeeStatusEnum::ON_LEAVE->value,
            'basic_salary' => 90000,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'UpdatedName UpdatedLast',
            'status' => EmployeeStatusEnum::ON_LEAVE->value,
        ]);

        $this->assertDatabaseHas('employee_details', [
            'user_id' => $user->id,
            'gender' => GenderEnum::FEMALE->value,
            'employment_type' => EmploymentTypeEnum::CONTRACT->value,
            'basic_salary' => 90000,
        ]);

        $response->assertRedirect(route('employees.show', $user));
    }

    public function test_employee_status_can_be_changed(): void
    {
        $user = User::factory()->create(['status' => EmployeeStatusEnum::ACTIVE]);
        $user->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->patch(route('employees.status', $user), [
            'status' => EmployeeStatusEnum::TERMINATED->value,
        ]);

        $response->assertRedirect();
        $this->assertEquals(EmployeeStatusEnum::TERMINATED, $user->fresh()->status);
    }

    public function test_employee_can_be_soft_deleted_and_restored(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        // Delete
        $response = $this->actingAs($this->superAdmin)->delete(route('employees.destroy', $user));
        $response->assertRedirect(route('employees.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        // Restore
        $restoreResponse = $this->actingAs($this->superAdmin)->post(route('employees.restore', $user->id));
        $restoreResponse->assertRedirect(route('employees.index'));
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_employee_document_can_be_uploaded_and_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('Employee');
        EmployeeDetail::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
        ]);

        $file = UploadedFile::fake()->create('certificate.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->superAdmin)->post(route('employees.documents.store', $user), [
            'title' => 'Degree Certificate',
            'file' => $file,
            'expiry_date' => '2028-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employee_documents', [
            'user_id' => $user->id,
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
