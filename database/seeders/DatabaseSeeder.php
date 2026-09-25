<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            CurrencySeeder::class,
            LanguageSeeder::class,
            PermissionSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            ShiftSeeder::class,
            WeekendSeeder::class,
            HolidaySeeder::class,
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@erp.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'phone' => '+8801700000000',
                'status' => 'active',
                'time_zone' => 'UTC',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['Super Admin']);
    }
}
