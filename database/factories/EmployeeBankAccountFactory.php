<?php

namespace Database\Factories;

use App\Models\EmployeeBankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeBankAccount>
 */
class EmployeeBankAccountFactory extends Factory
{
    protected $model = EmployeeBankAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank' => fake()->randomElement(['Dutch-Bangla Bank', 'BRAC Bank', 'City Bank', 'Eastern Bank PLC', 'Islami Bank Bangladesh']),
            'branch' => fake()->randomElement(['Gulshan Branch', 'Dhanmondi Branch', 'Banani Branch', 'Uttara Branch', 'Motijheel Branch']),
            'account_name' => fake()->name(),
            'account_no' => fake()->numerify('102##############'),
            'routing_number' => fake()->numerify('090######'),
            'swift_code' => fake()->bothify('????BDDH'),
            'is_primary' => true,
        ];
    }
}
