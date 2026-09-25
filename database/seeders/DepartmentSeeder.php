<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Software Engineering',
                'code' => 'ENG',
                'head_id' => null,
                'description' => 'Product development, architecture, core engineering, and quality assurance.',
                'status' => 'active',
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'head_id' => null,
                'description' => 'Recruitment, employee relations, company culture, and HR operations.',
                'status' => 'active',
            ],
            [
                'name' => 'Finance & Accounts',
                'code' => 'FIN',
                'head_id' => null,
                'description' => 'Financial planning, accounting, payroll management, and reporting.',
                'status' => 'active',
            ],
            [
                'name' => 'Sales & Marketing',
                'code' => 'SAL',
                'head_id' => null,
                'description' => 'Business development, client acquisition, branding, and marketing campaigns.',
                'status' => 'active',
            ],
            [
                'name' => 'Operations & Administration',
                'code' => 'OPS',
                'head_id' => null,
                'description' => 'Office administration, logistics, and operational infrastructure.',
                'status' => 'active',
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
