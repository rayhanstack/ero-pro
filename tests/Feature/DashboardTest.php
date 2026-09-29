<?php

namespace Tests\Feature;

use App\Enums\EmployeeStatusEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@erp.test',
            'name' => 'Admin User',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->employee = User::factory()->create([
            'email' => 'staff@erp.test',
            'name' => 'Staff Member',
            'status' => EmployeeStatusEnum::ACTIVE,
        ]);
        $this->employee->assignRole('Employee');
    }


    public function test_super_admin_can_render_dashboard_with_aggregated_real_data(): void
    {
        $this->actingAs($this->admin);

        $client = Client::create([
            'code' => Client::generateCode(),
            'company_name' => 'Acme Corporation',
            'contact_name' => 'John Doe',
            'email' => 'client@acme.test',
        ]);

        $project = Project::create([
            'code' => Project::generateCode(),
            'client_id' => $client->id,
            'name' => 'Dashboard Overhaul',
            'status' => ProjectStatusEnum::ACTIVE,
            'progress' => 60,
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Build Dashboard Service',
            'status' => TaskStatusEnum::IN_PROGRESS,
            'created_by' => $this->admin->id,
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-DASH-01',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 5000.00,
            'total_amount' => 5000.00,
            'paid_amount' => 3000.00,
            'due_amount' => 2000.00,
            'status' => InvoiceStatusEnum::PARTIAL,
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Acme Corporation');
        $response->assertSee('Dashboard Overhaul');
        $response->assertSee('Revenue vs Expense Overview');
        $response->assertSee('Project Status');
        $response->assertSee('Today\'s Workforce Overview');
    }

    public function test_dashboard_service_caches_data_for_five_minutes(): void
    {
        $service = app(DashboardService::class);
        $data1 = $service->getDashboardData($this->admin);

        $this->assertTrue(Cache::has("dashboard_user_{$this->admin->id}_data"));

        $cachedData = Cache::get("dashboard_user_{$this->admin->id}_data");
        $this->assertEquals($data1['project_stats']['active_projects'], $cachedData['project_stats']['active_projects']);

        $service->clearCache($this->admin->id);
        $this->assertFalse(Cache::has("dashboard_user_{$this->admin->id}_data"));
    }

    public function test_employee_without_admin_permissions_sees_personalized_widgets(): void
    {
        $this->actingAs($this->employee);

        $task = Task::create([
            'title' => 'Employee Personal Task',
            'status' => TaskStatusEnum::TODO,
            'created_by' => $this->admin->id,
        ]);
        $task->assignees()->attach($this->employee->id);

        $meeting = Meeting::create([
            'title' => 'Sprint Planning Sync',
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'organizer_id' => $this->employee->id,
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('My Pending Tasks');
        $response->assertSee('Sprint Planning Sync');
        $response->assertSee('Employee Personal Task');
    }

    public function test_revenue_chart_and_project_status_doughnut_contain_valid_structure(): void
    {
        $service = app(DashboardService::class);
        $data = $service->getDashboardData($this->admin);

        $this->assertArrayHasKey('revenue_chart', $data);
        $this->assertCount(6, $data['revenue_chart']['labels']);
        $this->assertCount(6, $data['revenue_chart']['revenues']);
        $this->assertCount(6, $data['revenue_chart']['expenses']);

        $this->assertArrayHasKey('project_status_chart', $data);
        $this->assertArrayHasKey('labels', $data['project_status_chart']);
        $this->assertArrayHasKey('data', $data['project_status_chart']);
        $this->assertArrayHasKey('colors', $data['project_status_chart']);
        $this->assertArrayHasKey('total', $data['project_status_chart']);
    }
}
