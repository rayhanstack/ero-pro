<?php

namespace Database\Factories;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(GenderEnum::cases());
        $firstName = $gender === GenderEnum::FEMALE ? fake()->firstNameFemale() : fake()->firstNameMale();
        $lastName = fake()->lastName();
        $joiningDate = fake()->dateTimeBetween('-4 years', '-1 months');

        return [
            'user_id' => null,
            'emp_code' => null, // Booted callback will generate EMP-0001, etc.
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+88017########'),
            'dob' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'gender' => $gender,
            'marital_status' => fake()->randomElement(MaritalStatusEnum::cases()),
            'nid' => fake()->numerify('#############'),
            'blood_group' => fake()->randomElement(BloodGroupEnum::cases()),
            'avatar' => null,
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
            'status' => EmployeeStatusEnum::ACTIVE,
            'basic_salary' => fake()->randomElement([35000, 45000, 55000, 75000, 95000, 120000]),
            'country_id' => Country::first()?->id,
            'state_id' => State::first()?->id,
            'city_id' => City::first()?->id,
            'present_address' => fake()->streetAddress() . ', ' . fake()->city(),
            'permanent_address' => fake()->streetAddress() . ', ' . fake()->city(),
        ];
    }
}
