<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+6 months');
        $endDate = (clone $startDate)->modify('+1 day');

        return [
            'title' => fake()->unique()->words(3, true) . ' Day',
            'from_date' => $startDate->format('Y-m-d'),
            'to_date' => $endDate->format('Y-m-d'),
            'type' => fake()->randomElement(['public', 'company']),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
