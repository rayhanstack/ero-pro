<?php

namespace Database\Seeders;

use App\Models\Weekend;
use Illuminate\Database\Seeder;

class WeekendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = [
            ['day_of_week' => 0, 'name' => 'Sunday', 'is_weekend' => false],
            ['day_of_week' => 1, 'name' => 'Monday', 'is_weekend' => false],
            ['day_of_week' => 2, 'name' => 'Tuesday', 'is_weekend' => false],
            ['day_of_week' => 3, 'name' => 'Wednesday', 'is_weekend' => false],
            ['day_of_week' => 4, 'name' => 'Thursday', 'is_weekend' => false],
            ['day_of_week' => 5, 'name' => 'Friday', 'is_weekend' => true], // Bangladesh weekend
            ['day_of_week' => 6, 'name' => 'Saturday', 'is_weekend' => false],
        ];

        foreach ($days as $day) {
            Weekend::updateOrCreate(
                ['day_of_week' => $day['day_of_week']],
                $day
            );
        }
    }
}
