<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = (int) date('Y');

        $holidays = [
            [
                'title' => 'International Mother Language Day',
                'from_date' => "{$year}-02-21",
                'to_date' => "{$year}-02-21",
                'type' => 'public',
                'description' => 'National Martyrs Day & International Mother Language Day.',
                'status' => 'active',
            ],
            [
                'title' => 'Independence Day',
                'from_date' => "{$year}-03-26",
                'to_date' => "{$year}-03-26",
                'type' => 'public',
                'description' => 'National Day of Independence.',
                'status' => 'active',
            ],
            [
                'title' => 'Bengali New Year (Pohela Boishakh)',
                'from_date' => "{$year}-04-14",
                'to_date' => "{$year}-04-14",
                'type' => 'public',
                'description' => 'First day of the Bengali calendar.',
                'status' => 'active',
            ],
            [
                'title' => 'May Day (International Workers Day)',
                'from_date' => "{$year}-05-01",
                'to_date' => "{$year}-05-01",
                'type' => 'public',
                'description' => 'International Labor Day celebration.',
                'status' => 'active',
            ],
            [
                'title' => 'Eid-ul-Fitr Holidays',
                'from_date' => "{$year}-04-01",
                'to_date' => "{$year}-04-03",
                'type' => 'public',
                'description' => 'Islamic festival celebration break.',
                'status' => 'active',
            ],
            [
                'title' => 'Victory Day',
                'from_date' => "{$year}-12-16",
                'to_date' => "{$year}-12-16",
                'type' => 'public',
                'description' => 'National Victory Day of Bangladesh.',
                'status' => 'active',
            ],
            [
                'title' => 'Company Annual Foundation Day',
                'from_date' => "{$year}-10-15",
                'to_date' => "{$year}-10-15",
                'type' => 'company',
                'description' => 'Annual corporate milestone celebration and team break.',
                'status' => 'active',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['title' => $holiday['title'], 'from_date' => $holiday['from_date']],
                $holiday
            );
        }
    }
}
