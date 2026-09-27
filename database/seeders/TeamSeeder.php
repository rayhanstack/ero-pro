<?php

namespace Database\Seeders;

use App\Enums\TeamStatusEnum;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        $lead1 = $users->firstWhere('email', 'tariqul.lead@erp.test') ?? $users->first();
        $lead2 = $users->skip(1)->first() ?? $lead1;
        $lead3 = $users->skip(2)->first() ?? $lead1;
        $lead4 = $users->skip(3)->first() ?? $lead1;

        $teamsData = [
            [
                'name' => 'Frontend Engineering Squad',
                'lead_id' => $lead1?->id,
                'description' => 'Specializes in responsive UI/UX architecture, Blade component libraries, and frontend performance optimization.',
                'status' => TeamStatusEnum::ACTIVE,
                'member_indices' => [0, 1, 2, 3],
            ],
            [
                'name' => 'Backend & API Core',
                'lead_id' => $lead2?->id,
                'description' => 'Responsible for microservices orchestration, Laravel REST APIs, caching layers, and database optimization.',
                'status' => TeamStatusEnum::ACTIVE,
                'member_indices' => [1, 2, 4, 5],
            ],
            [
                'name' => 'Mobile Applications (iOS & Android)',
                'lead_id' => $lead3?->id,
                'description' => 'Cross-platform Flutter and native mobile client solutions for enterprise workforce management.',
                'status' => TeamStatusEnum::ACTIVE,
                'member_indices' => [2, 3, 6, 7],
            ],
            [
                'name' => 'DevOps & Cloud Infrastructure',
                'lead_id' => $lead4?->id,
                'description' => 'Automated CI/CD pipelines, Docker containerization, Kubernetes cluster management, and system monitoring.',
                'status' => TeamStatusEnum::ACTIVE,
                'member_indices' => [0, 4, 5, 8],
            ],
            [
                'name' => 'Quality Assurance & Automation',
                'lead_id' => $lead1?->id,
                'description' => 'End-to-end integration testing, Pest/PHPUnit suites, security scanning, and automated regression testing.',
                'status' => TeamStatusEnum::ACTIVE,
                'member_indices' => [3, 4, 6, 9],
            ],
            [
                'name' => 'Legacy Migration Taskforce',
                'lead_id' => $lead2?->id,
                'description' => 'Temporary taskforce to migrate historical ERP v1 records to the new system.',
                'status' => TeamStatusEnum::INACTIVE,
                'member_indices' => [1, 7],
            ],
        ];

        foreach ($teamsData as $data) {
            $memberIndices = $data['member_indices'] ?? [];
            unset($data['member_indices']);

            $team = Team::create($data);

            $memberIds = [];
            foreach ($memberIndices as $idx) {
                if (isset($users[$idx])) {
                    $memberIds[] = $users[$idx]->id;
                }
            }

            if (! empty($memberIds)) {
                $team->members()->sync($memberIds);
            }
        }
    }
}
