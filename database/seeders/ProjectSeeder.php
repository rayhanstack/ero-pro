<?php

namespace Database\Seeders;

use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $currencies = Currency::all();
        $users = User::all();

        $defaultCurrencyId = $currencies->firstWhere('is_default', true)?->id ?? $currencies->first()?->id;
        $manager1 = $users->firstWhere('email', 'manager@erp.test') ?? $users->first();
        $manager2 = $users->skip(1)->first() ?? $manager1;
        $manager3 = $users->skip(2)->first() ?? $manager1;

        $client1 = $clients->first();
        $client2 = $clients->skip(1)->first() ?? $client1;
        $client3 = $clients->skip(2)->first() ?? $client1;
        $client4 = $clients->skip(3)->first() ?? $client1;

        $projects = [
            [
                'code' => 'PRJ-0001',
                'name' => 'HRM & Payroll Module Modernization',
                'client_id' => $client1?->id,
                'description' => 'Comprehensive refactoring of human resource workflows, multi-tier attendance regularization, overtime calculation, and automated salary slip generation for enterprise deployment.',
                'start_date' => '2026-01-10',
                'deadline' => '2026-06-30',
                'budget' => 45000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::HIGH,
                'status' => ProjectStatusEnum::ACTIVE,
                'progress' => 65,
                'manager_id' => $manager1?->id,
                'members' => [
                    ['role' => 'Lead Fullstack Engineer', 'user_idx' => 0],
                    ['role' => 'Backend Architect', 'user_idx' => 1],
                    ['role' => 'Frontend Specialist', 'user_idx' => 2],
                    ['role' => 'QA Engineer', 'user_idx' => 3],
                ],
                'milestones' => [
                    ['title' => 'Architecture & Schema Specification', 'due_date' => '2026-02-15', 'cost' => 10000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Finalized ER diagram, enum contracts, and API specifications.'],
                    ['title' => 'Attendance & Leave Processing Engine', 'due_date' => '2026-04-10', 'cost' => 15000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Daily check-in / check-out calculations and yearly leave quota distribution.'],
                    ['title' => 'Payroll Tax & Automated Payslips', 'due_date' => '2026-05-30', 'cost' => 12000.00, 'status' => MilestoneStatusEnum::INCOMPLETE, 'description' => 'Automated salary breakdown, allowances, deductions, and PDF receipt rendering.'],
                    ['title' => 'Security Audit & Final Release', 'due_date' => '2026-06-30', 'cost' => 8000.00, 'status' => MilestoneStatusEnum::INCOMPLETE, 'description' => 'External penetration testing, code hardening, and client production migration.'],
                ],
                'files' => [
                    ['file_name' => 'Architecture_Blueprint_v2.pdf', 'file_path' => 'projects/files/sample_arch.pdf', 'file_size' => 2450000, 'file_type' => 'pdf'],
                    ['file_name' => 'API_Specs_Swagger.json', 'file_path' => 'projects/files/swagger.json', 'file_size' => 180000, 'file_type' => 'json'],
                ],
            ],
            [
                'code' => 'PRJ-0002',
                'name' => 'Corporate E-Commerce Portal Redesign',
                'client_id' => $client2?->id,
                'description' => 'Next-generation storefront rebuild featuring high-speed product catalog filtering, multi-currency checkout, and seamless inventory sync across regional fulfillment warehouses.',
                'start_date' => '2026-02-01',
                'deadline' => '2026-08-15',
                'budget' => 85000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::URGENT,
                'status' => ProjectStatusEnum::ACTIVE,
                'progress' => 40,
                'manager_id' => $manager2?->id,
                'members' => [
                    ['role' => 'Tech Lead', 'user_idx' => 1],
                    ['role' => 'UI/UX Designer', 'user_idx' => 4],
                    ['role' => 'Payment Gateway Specialist', 'user_idx' => 5],
                ],
                'milestones' => [
                    ['title' => 'Design System & High-fidelity Prototypes', 'due_date' => '2026-03-20', 'cost' => 20000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Figma design tokens, component library, and stakeholder sign-off.'],
                    ['title' => 'Catalog & ElasticSearch Integration', 'due_date' => '2026-05-15', 'cost' => 30000.00, 'status' => MilestoneStatusEnum::INCOMPLETE, 'description' => 'Real-time multi-facet search indexing and product variant management.'],
                    ['title' => 'Stripe & PayPal Multi-currency Checkout', 'due_date' => '2026-07-10', 'cost' => 25000.00, 'status' => MilestoneStatusEnum::INCOMPLETE, 'description' => 'Secure webhook processing, fraud screening, and 3D Secure verification.'],
                ],
                'files' => [
                    ['file_name' => 'Figma_Export_Specs.zip', 'file_path' => 'projects/files/figma_specs.zip', 'file_size' => 14500000, 'file_type' => 'zip'],
                ],
            ],
            [
                'code' => 'PRJ-0003',
                'name' => 'Enterprise iOS & Android Mobile Client',
                'client_id' => $client3?->id,
                'description' => 'Cross-platform mobile application with offline-first synchronization, push notifications, geofenced clock-in, and biometric authentication.',
                'start_date' => '2026-03-01',
                'deadline' => '2026-09-30',
                'budget' => 60000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::HIGH,
                'status' => ProjectStatusEnum::PLANNING,
                'progress' => 15,
                'manager_id' => $manager3?->id,
                'members' => [
                    ['role' => 'Mobile Lead', 'user_idx' => 2],
                    ['role' => 'Flutter Developer', 'user_idx' => 6],
                    ['role' => 'Mobile QA Specialist', 'user_idx' => 7],
                ],
                'milestones' => [
                    ['title' => 'Mobile Architecture & Offline SQLite Sync', 'due_date' => '2026-04-15', 'cost' => 18000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Conflict resolution strategy and local caching foundation.'],
                    ['title' => 'Biometric Authentication & Geofencing', 'due_date' => '2026-06-30', 'cost' => 22000.00, 'status' => MilestoneStatusEnum::INCOMPLETE, 'description' => 'Face ID / Fingerprint unlock and background GPS fence check.'],
                ],
                'files' => [],
            ],
            [
                'code' => 'PRJ-0004',
                'name' => 'Cloud Infrastructure & Kubernetes Migration',
                'client_id' => $client1?->id,
                'description' => 'Zero-downtime database and microservices migration to AWS EKS with Terraform infrastructure-as-code and automated canary deployments.',
                'start_date' => '2025-11-01',
                'deadline' => '2026-03-15',
                'budget' => 38000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::MEDIUM,
                'status' => ProjectStatusEnum::COMPLETED,
                'progress' => 100,
                'manager_id' => $manager1?->id,
                'members' => [
                    ['role' => 'DevOps Architect', 'user_idx' => 0],
                    ['role' => 'Site Reliability Engineer', 'user_idx' => 4],
                ],
                'milestones' => [
                    ['title' => 'Terraform Provisioning', 'due_date' => '2025-12-15', 'cost' => 15000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'VPC, EKS cluster, RDS PostgreSQL, and Redis clusters provisioned.'],
                    ['title' => 'Database Cutover & Load Verification', 'due_date' => '2026-03-01', 'cost' => 23000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Zero-downtime logical replication and performance benchmarking.'],
                ],
                'files' => [],
            ],
            [
                'code' => 'PRJ-0005',
                'name' => 'AI Customer Insights & Analytics Engine',
                'client_id' => $client4?->id,
                'description' => 'Predictive churn modeling, automated customer segmentation, and natural language query generation over relational warehouse stores.',
                'start_date' => '2026-02-15',
                'deadline' => '2026-10-31',
                'budget' => 95000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::LOW,
                'status' => ProjectStatusEnum::ON_HOLD,
                'progress' => 25,
                'manager_id' => $manager2?->id,
                'members' => [
                    ['role' => 'Data Scientist', 'user_idx' => 3],
                    ['role' => 'MLOps Engineer', 'user_idx' => 5],
                ],
                'milestones' => [
                    ['title' => 'Feature Store & Data Pipeline', 'due_date' => '2026-04-30', 'cost' => 35000.00, 'status' => MilestoneStatusEnum::COMPLETE, 'description' => 'Real-time event streaming and feature ETL.'],
                ],
                'files' => [],
            ],
            [
                'code' => 'PRJ-0006',
                'name' => 'Legacy CRM Integration & Data Sync',
                'client_id' => $client2?->id,
                'description' => 'Bi-directional synchronization between Salesforce CRM and internal ERP customer balances.',
                'start_date' => '2025-08-01',
                'deadline' => '2025-12-31',
                'budget' => 22000.00,
                'currency_id' => $defaultCurrencyId,
                'priority' => ProjectPriorityEnum::MEDIUM,
                'status' => ProjectStatusEnum::CANCELLED,
                'progress' => 10,
                'manager_id' => $manager3?->id,
                'members' => [
                    ['role' => 'Integration Developer', 'user_idx' => 1],
                ],
                'milestones' => [],
                'files' => [],
            ],
        ];

        foreach ($projects as $projectData) {
            $members = $projectData['members'] ?? [];
            $milestones = $projectData['milestones'] ?? [];
            $files = $projectData['files'] ?? [];

            unset($projectData['members'], $projectData['milestones'], $projectData['files']);

            $project = Project::create($projectData);

            // Members
            foreach ($members as $member) {
                $u = $users[$member['user_idx']] ?? $users->first();
                if ($u) {
                    ProjectMember::firstOrCreate([
                        'project_id' => $project->id,
                        'employee_id' => $u->id,
                    ], [
                        'role' => $member['role'],
                    ]);
                }
            }

            // Milestones
            foreach ($milestones as $milestone) {
                ProjectMilestone::create([
                    'project_id' => $project->id,
                    'title' => $milestone['title'],
                    'due_date' => $milestone['due_date'],
                    'cost' => $milestone['cost'] ?? 0,
                    'status' => $milestone['status'],
                    'description' => $milestone['description'] ?? null,
                ]);
            }

            // Files
            foreach ($files as $file) {
                ProjectFile::create([
                    'project_id' => $project->id,
                    'user_id' => $project->manager_id ?? $users->first()?->id,
                    'file_name' => $file['file_name'],
                    'file_path' => $file['file_path'],
                    'file_size' => $file['file_size'],
                    'file_type' => $file['file_type'],
                ]);
            }
        }
    }
}
