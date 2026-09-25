<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Regular Day Shift',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'grace_minutes' => 15,
                'description' => 'Standard corporate office shift.',
                'status' => 'active',
            ],
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'grace_minutes' => 10,
                'description' => 'Early morning operational roster.',
                'status' => 'active',
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'grace_minutes' => 10,
                'description' => 'Afternoon/evening support shift.',
                'status' => 'active',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'grace_minutes' => 15,
                'description' => 'Overnight technical support roster.',
                'status' => 'active',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }
}
