<?php

namespace Database\Seeders;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\Project\ProjectService;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with('members')->get();
        $users = User::all();

        if ($projects->isEmpty() || $users->isEmpty()) {
            return;
        }

        $creator = $users->firstWhere('email', 'admin@erp.test') ?? $users->first();

        $tasksData = [
            // Project 1 (HRM & Payroll Module Modernization)
            [
                'project_code' => 'PRJ-0001',
                'title' => 'Design attendance regularization approval matrix',
                'description' => 'Create tiered approval rules for manager, HR, and admin with automatic overtime calculation triggers.',
                'priority' => TaskPriorityEnum::HIGH,
                'status' => TaskStatusEnum::DONE,
                'start_date' => now()->subDays(15)->format('Y-m-d'),
                'due_date' => now()->subDays(2)->format('Y-m-d'),
                'estimated_hours' => 16.0,
                'position' => 1,
                'checklists' => [
                    ['title' => 'Draft workflow flowchart', 'is_completed' => true],
                    ['title' => 'Define DB schema for regularization requests', 'is_completed' => true],
                    ['title' => 'Implement manager approval middleware', 'is_completed' => true],
                ],
                'comments' => [
                    ['user' => $users[1] ?? $creator, 'comment' => 'Flowchart approved by HR stakeholders.'],
                    ['user' => $creator, 'comment' => 'Ready for production deployment.'],
                ],
            ],
            [
                'project_code' => 'PRJ-0001',
                'title' => 'Build automated PDF pay slip generator',
                'description' => 'Generate monthly printable PDF payslips with dynamic breakdown of base salary, allowances, tax brackets, and loan deductions.',
                'priority' => TaskPriorityEnum::URGENT,
                'status' => TaskStatusEnum::IN_PROGRESS,
                'start_date' => now()->subDays(5)->format('Y-m-d'),
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'estimated_hours' => 24.0,
                'position' => 1,
                'checklists' => [
                    ['title' => 'Design Blade PDF template with company branding', 'is_completed' => true],
                    ['title' => 'Calculate dynamic tax slab deductions', 'is_completed' => true],
                    ['title' => 'Integrate DomPDF rendering pipeline', 'is_completed' => false],
                    ['title' => 'Write queue job for batch email delivery', 'is_completed' => false],
                ],
                'comments' => [
                    ['user' => $users[2] ?? $creator, 'comment' => 'DomPDF styling in progress, checking table page-break issues.'],
                ],
            ],
            [
                'project_code' => 'PRJ-0001',
                'title' => 'Implement Biometric Fingerprint & Face Sync API',
                'description' => 'Build webhook receivers and cron polling job to ingest attendance logs from ZKTeco biometric devices.',
                'priority' => TaskPriorityEnum::MEDIUM,
                'status' => TaskStatusEnum::REVIEW,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'due_date' => now()->addDays(1)->format('Y-m-d'),
                'estimated_hours' => 20.0,
                'position' => 1,
                'checklists' => [
                    ['title' => 'Establish device communication protocol', 'is_completed' => true],
                    ['title' => 'Handle punch duplicate deduplication', 'is_completed' => true],
                    ['title' => 'Stress test with 10k punch logs', 'is_completed' => false],
                ],
                'comments' => [
                    ['user' => $users[0] ?? $creator, 'comment' => 'Under QA stress testing right now.'],
                ],
            ],
            [
                'project_code' => 'PRJ-0001',
                'title' => 'Conduct penetration testing on payroll export endpoints',
                'description' => 'Verify role-based access restrictions and ensure bank account details cannot leak through unauthorized exports.',
                'priority' => TaskPriorityEnum::HIGH,
                'status' => TaskStatusEnum::TODO,
                'start_date' => now()->addDays(2)->format('Y-m-d'),
                'due_date' => now()->addDays(10)->format('Y-m-d'),
                'estimated_hours' => 12.0,
                'position' => 1,
                'checklists' => [
                    ['title' => 'Audit controller authorization gates', 'is_completed' => false],
                    ['title' => 'Validate CSRF & IDOR vulnerabilities', 'is_completed' => false],
                ],
                'comments' => [],
            ],

            // Project 2 (Corporate E-Commerce Portal Redesign)
            [
                'project_code' => 'PRJ-0002',
                'title' => 'Stripe 3D Secure 2.0 Webhook Handler',
                'description' => 'Implement idempotent webhook listener for Stripe payment intent success, failure, and dispute events.',
                'priority' => TaskPriorityEnum::URGENT,
                'status' => TaskStatusEnum::IN_PROGRESS,
                'start_date' => now()->subDays(4)->format('Y-m-d'),
                'due_date' => now()->addDays(2)->format('Y-m-d'),
                'estimated_hours' => 18.0,
                'position' => 2,
                'checklists' => [
                    ['title' => 'Verify webhook cryptographic signature', 'is_completed' => true],
                    ['title' => 'Update order status atomically in DB transaction', 'is_completed' => true],
                    ['title' => 'Send order confirmation email notification', 'is_completed' => false],
                ],
                'comments' => [
                    ['user' => $creator, 'comment' => 'Tested in Stripe test mode with 3DS challenge.'],
                ],
            ],
            [
                'project_code' => 'PRJ-0002',
                'title' => 'Product Catalog Filter Component with Instant Search',
                'description' => 'Build reactive filter sidebar with price sliders, category multi-select, brand pills, and stock availability toggles.',
                'priority' => TaskPriorityEnum::MEDIUM,
                'status' => TaskStatusEnum::DONE,
                'start_date' => now()->subDays(10)->format('Y-m-d'),
                'due_date' => now()->subDays(1)->format('Y-m-d'),
                'estimated_hours' => 30.0,
                'position' => 2,
                'checklists' => [
                    ['title' => 'Setup debounced search query listener', 'is_completed' => true],
                    ['title' => 'Sync URL query parameters on filter change', 'is_completed' => true],
                    ['title' => 'Optimize database indexes on products table', 'is_completed' => true],
                ],
                'comments' => [],
            ],
            [
                'project_code' => 'PRJ-0002',
                'title' => 'Multi-warehouse stock reservation on checkout',
                'description' => 'Prevent race conditions when multiple customers buy the last item simultaneously using pessimistic locking.',
                'priority' => TaskPriorityEnum::HIGH,
                'status' => TaskStatusEnum::TODO,
                'start_date' => now()->addDays(1)->format('Y-m-d'),
                'due_date' => now()->addDays(8)->format('Y-m-d'),
                'estimated_hours' => 14.0,
                'position' => 2,
                'checklists' => [
                    ['title' => 'Implement lockForUpdate on cart items', 'is_completed' => false],
                    ['title' => 'Setup 15-minute temporary inventory reservation expiration', 'is_completed' => false],
                ],
                'comments' => [],
            ],

            // Project 3 (Enterprise iOS & Android Mobile Client)
            [
                'project_code' => 'PRJ-0003',
                'title' => 'Geofenced Mobile Clock-in / Clock-out',
                'description' => 'Check device GPS coordinates against company branch polygon boundary before permitting clock-in.',
                'priority' => TaskPriorityEnum::HIGH,
                'status' => TaskStatusEnum::IN_PROGRESS,
                'start_date' => now()->subDays(3)->format('Y-m-d'),
                'due_date' => now()->addDays(5)->format('Y-m-d'),
                'estimated_hours' => 22.0,
                'position' => 3,
                'checklists' => [
                    ['title' => 'Haversine distance calculation helper', 'is_completed' => true],
                    ['title' => 'Fake GPS and mock location detection', 'is_completed' => false],
                    ['title' => 'Offline queue sync on network restore', 'is_completed' => false],
                ],
                'comments' => [
                    ['user' => $users[1] ?? $creator, 'comment' => 'Branch GPS radius set to 100 meters.'],
                ],
            ],
            [
                'project_code' => 'PRJ-0003',
                'title' => 'Biometric Fingerprint & FaceID Login Flow',
                'description' => 'Store encrypted refresh token in iOS Keychain and Android Keystore with biometric prompt challenge.',
                'priority' => TaskPriorityEnum::MEDIUM,
                'status' => TaskStatusEnum::TODO,
                'start_date' => now()->addDays(4)->format('Y-m-d'),
                'due_date' => now()->addDays(14)->format('Y-m-d'),
                'estimated_hours' => 16.0,
                'position' => 3,
                'checklists' => [
                    ['title' => 'Configure LocalAuthentication framework for iOS', 'is_completed' => false],
                    ['title' => 'Configure BiometricPrompt for Android', 'is_completed' => false],
                    ['title' => 'Fallback to PIN/Password on 3 failed attempts', 'is_completed' => false],
                ],
                'comments' => [],
            ],
        ];

        $projectService = app(ProjectService::class);

        foreach ($tasksData as $data) {
            $project = $projects->firstWhere('code', $data['project_code']);

            $task = Task::create([
                'project_id' => $project?->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'status' => $data['status'],
                'start_date' => $data['start_date'],
                'due_date' => $data['due_date'],
                'estimated_hours' => $data['estimated_hours'],
                'created_by' => $creator->id,
                'position' => $data['position'],
            ]);

            // Assign members
            if ($project && $project->members->isNotEmpty()) {
                $assignees = $project->members->take(2)->pluck('id')->toArray();
                $task->assignees()->sync($assignees);
            } else {
                $task->assignees()->sync([$creator->id]);
            }

            // Checklists
            foreach ($data['checklists'] as $idx => $chk) {
                TaskChecklist::create([
                    'task_id' => $task->id,
                    'title' => $chk['title'],
                    'is_completed' => $chk['is_completed'],
                    'position' => $idx + 1,
                ]);
            }

            // Comments
            foreach ($data['comments'] as $comm) {
                TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => $comm['user']->id,
                    'comment' => $comm['comment'],
                ]);
            }

            // Recalculate project progress
            if ($project) {
                $projectService->recalculateProgress($project);
            }
        }
    }
}
