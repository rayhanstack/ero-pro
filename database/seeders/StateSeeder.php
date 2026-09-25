<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = DB::table('countries')->pluck('id', 'iso2');
        $now = now();

        $statesMap = [
            'BD' => [
                'Dhaka',
                'Chattogram',
                'Rajshahi',
                'Khulna',
                'Barishal',
                'Sylhet',
                'Rangpur',
                'Mymensingh',
            ],
            'US' => [
                'California',
                'New York',
                'Texas',
                'Florida',
                'Illinois',
                'Washington',
            ],
            'GB' => [
                'England',
                'Scotland',
                'Wales',
                'Northern Ireland',
            ],
            'AE' => [
                'Dubai',
                'Abu Dhabi',
                'Sharjah',
                'Ajman',
            ],
            'IN' => [
                'West Bengal',
                'Maharashtra',
                'Delhi',
                'Karnataka',
                'Tamil Nadu',
            ],
            'CA' => [
                'Ontario',
                'Quebec',
                'British Columbia',
                'Alberta',
            ],
        ];

        $records = [];

        foreach ($statesMap as $iso2 => $states) {
            $countryId = $countries[$iso2] ?? null;
            if ($countryId) {
                foreach ($states as $stateName) {
                    $records[] = [
                        'name' => $stateName,
                        'country_id' => $countryId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($records)) {
            DB::table('states')->insertOrIgnore($records);
        }
    }
}
