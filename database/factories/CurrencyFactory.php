<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => fake()->currencyCode(),
            'code' => fake()->unique()->currencyCode(),
            'symbol' => '$',
        ];
    }
}
