<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native' => 'English',
                'rtl' => 0,
                'status' => 'active',
                'json_exist' => 1,
                'is_default' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'bn',
                'name' => 'Bangla',
                'native' => 'বাংলা',
                'rtl' => 0,
                'status' => 'active',
                'json_exist' => 1,
                'is_default' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('languages')->insertOrIgnore($languages);
    }
}
