<?php

namespace Database\Factories;

use App\Enums\EmployeeStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => null,
            'phone' => fake()->phoneNumber(),
            'status' => EmployeeStatusEnum::ACTIVE,
            'time_zone' => 'UTC',
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::TERMINATED,
        ]);
    }

    /**
     * Indicate that the user account is on leave.
     */
    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::ON_LEAVE,
        ]);
    }

    /**
     * Indicate that the user account is resigned.
     */
    public function resigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::RESIGNED,
        ]);
    }

    /**
     * Indicate that the user account is terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::TERMINATED,
        ]);
    }
}
