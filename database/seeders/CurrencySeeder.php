<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $currencies = [
            ['name' => 'Bangladeshi Taka', 'code' => 'BDT', 'symbol' => '৳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('currencies')->insertOrIgnore($currencies);
    }
}
