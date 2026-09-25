<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $countries = [
            ['name' => 'Bangladesh', 'iso2' => 'BD', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'United States', 'iso2' => 'US', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'United Kingdom', 'iso2' => 'GB', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'India', 'iso2' => 'IN', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Canada', 'iso2' => 'CA', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('countries')->insertOrIgnore($countries);
    }
}
