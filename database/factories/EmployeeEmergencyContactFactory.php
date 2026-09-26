<?php

namespace Database\Factories;

use App\Models\EmployeeEmergencyContact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeEmergencyContact>
 */
class EmployeeEmergencyContactFactory extends Factory
{
    protected $model = EmployeeEmergencyContact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Friend']),
            'phone' => fake()->numerify('+88018########'),
            'alt_phone' => fake()->optional()->numerify('+88019########'),
            'address' => fake()->streetAddress() . ', ' . fake()->city(),
        ];
    }
}
