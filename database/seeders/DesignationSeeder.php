<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eng = Department::where('code', 'ENG')->first();
        $hr = Department::where('code', 'HR')->first();
        $fin = Department::where('code', 'FIN')->first();
        $sal = Department::where('code', 'SAL')->first();
        $ops = Department::where('code', 'OPS')->first();

        $designations = [];

        if ($eng) {
            $designations[] = ['name' => 'Junior Software Engineer', 'department_id' => $eng->id, 'level' => 1, 'description' => 'Entry-level software engineer.', 'status' => 'active'];
            $designations[] = ['name' => 'Software Engineer', 'department_id' => $eng->id, 'level' => 2, 'description' => 'Mid-level software engineer.', 'status' => 'active'];
            $designations[] = ['name' => 'Senior Software Engineer', 'department_id' => $eng->id, 'level' => 3, 'description' => 'Senior engineer leading feature modules.', 'status' => 'active'];
            $designations[] = ['name' => 'Tech Lead', 'department_id' => $eng->id, 'level' => 4, 'description' => 'Technical leader for engineering squad.', 'status' => 'active'];
            $designations[] = ['name' => 'QA Engineer', 'department_id' => $eng->id, 'level' => 2, 'description' => 'Quality assurance and automation.', 'status' => 'active'];
        }

        if ($hr) {
            $designations[] = ['name' => 'HR Executive', 'department_id' => $hr->id, 'level' => 1, 'description' => 'Handles daily HR tasks and documentation.', 'status' => 'active'];
            $designations[] = ['name' => 'HR Manager', 'department_id' => $hr->id, 'level' => 3, 'description' => 'Manages human resources department.', 'status' => 'active'];
        }

        if ($fin) {
            $designations[] = ['name' => 'Accountant', 'department_id' => $fin->id, 'level' => 2, 'description' => 'Handles bookkeeping and invoice processing.', 'status' => 'active'];
            $designations[] = ['name' => 'Finance Manager', 'department_id' => $fin->id, 'level' => 4, 'description' => 'Supervises fiscal planning and compliance.', 'status' => 'active'];
        }

        if ($sal) {
            $designations[] = ['name' => 'Sales Executive', 'department_id' => $sal->id, 'level' => 1, 'description' => 'Client outreach and product demonstration.', 'status' => 'active'];
            $designations[] = ['name' => 'Account Manager', 'department_id' => $sal->id, 'level' => 3, 'description' => 'Key customer relationships and growth.', 'status' => 'active'];
        }

        if ($ops) {
            $designations[] = ['name' => 'Office Administrator', 'department_id' => $ops->id, 'level' => 1, 'description' => 'Office facilities and operational support.', 'status' => 'active'];
        }

        foreach ($designations as $desig) {
            Designation::firstOrCreate(
                ['name' => $desig['name'], 'department_id' => $desig['department_id']],
                $desig
            );
        }
    }
}
