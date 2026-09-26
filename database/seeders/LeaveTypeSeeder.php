<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'days_per_year' => 10,
                'is_paid' => true,
                'carry_forward' => false,
                'max_carry' => 0,
                'status' => StatusEnum::ACTIVE,
                'color' => '#3b82f6', // blue
                'description' => 'For personal emergencies and short unforeseen leaves.',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'days_per_year' => 14,
                'is_paid' => true,
                'carry_forward' => false,
                'max_carry' => 0,
                'status' => StatusEnum::ACTIVE,
                'color' => '#ef4444', // red
                'description' => 'For medical illness and healthcare appointments with doctor certificate.',
            ],
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'days_per_year' => 15,
                'is_paid' => true,
                'carry_forward' => true,
                'max_carry' => 5,
                'status' => StatusEnum::ACTIVE,
                'color' => '#10b981', // emerald green
                'description' => 'Paid earned vacation leave. Up to 5 days carry forward allowed.',
            ],
            [
                'name' => 'Parental Leave',
                'code' => 'PL',
                'days_per_year' => 10,
                'is_paid' => true,
                'carry_forward' => false,
                'max_carry' => 0,
                'status' => StatusEnum::ACTIVE,
                'color' => '#8b5cf6', // purple
                'description' => 'For new parents upon birth or adoption of a child.',
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'days_per_year' => 0,
                'is_paid' => false,
                'carry_forward' => false,
                'max_carry' => 0,
                'status' => StatusEnum::ACTIVE,
                'color' => '#6b7280', // gray
                'description' => 'Approved time off without pay once annual quotas are exhausted.',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
