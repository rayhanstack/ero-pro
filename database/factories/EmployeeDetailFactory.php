<?php

namespace Database\Factories;

use App\Enums\BloodGroupEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\Shift;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeDetail>
 */
class EmployeeDetailFactory extends Factory
{
    protected $model = EmployeeDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(GenderEnum::cases());
        $joiningDate = fake()->dateTimeBetween('-4 years', '-1 months');

        return [
            'user_id' => User::factory(),
            'emp_code' => null, // Auto-generated in boot()
            'dob' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'gender' => $gender,
            'marital_status' => fake()->randomElement(MaritalStatusEnum::cases()),
            'nid' => fake()->numerify('#############'),
            'blood_group' => fake()->randomElement(BloodGroupEnum::cases()),
            'department_id' => Department::factory(),
            'designation_id' => function (array $attributes) {
                return Designation::factory()->create([
                    'department_id' => $attributes['department_id'] ?? Department::factory(),
                ])->id;
            },
            'shift_id' => Shift::factory(),
            'manager_id' => null,
            'joining_date' => $joiningDate->format('Y-m-d'),
            'confirmation_date' => (clone $joiningDate)->modify('+3 months')->format('Y-m-d'),
            'employment_type' => fake()->randomElement(EmploymentTypeEnum::cases()),
            'basic_salary' => fake()->randomElement([35000, 45000, 55000, 75000, 95000, 120000]),
            'country_id' => Country::first()?->id,
            'state_id' => State::first()?->id,
            'city_id' => City::first()?->id,
            'present_address' => fake()->streetAddress() . ', ' . fake()->city(),
            'permanent_address' => fake()->streetAddress() . ', ' . fake()->city(),
        ];
    }
}
