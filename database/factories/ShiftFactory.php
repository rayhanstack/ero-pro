<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Shift',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_minutes' => 15,
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
