<?php

namespace Database\Seeders;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            EmployeeSeeder::class,
            AttendanceSeeder::class,
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@erp.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '+8801700000000',
                'status' => EmployeeStatusEnum::ACTIVE,
                'time_zone' => 'Asia/Dhaka',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['Super Admin']);

        // Attach employee detail for admin if missing
        if (! $admin->detail) {
            $dept = Department::first();
            $desig = Designation::first();
            $shift = Shift::first();

            EmployeeDetail::create([
                'user_id' => $admin->id,
                'emp_code' => 'EMP-0000',
                'dob' => '1990-01-01',
                'gender' => GenderEnum::MALE,
                'blood_group' => BloodGroupEnum::A_POSITIVE,
                'department_id' => $dept?->id,
                'designation_id' => $desig?->id,
                'shift_id' => $shift?->id,
                'joining_date' => '2020-01-01',
                'employment_type' => EmploymentTypeEnum::FULL_TIME,
                'basic_salary' => 150000.00,
            ]);
        }
    }
}
