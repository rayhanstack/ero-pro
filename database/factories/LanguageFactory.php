<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->languageCode(),
            'name' => fake()->word(),
            'native' => fake()->word(),
            'rtl' => 0,
            'status' => 'active',
            'json_exist' => 1,
            'is_default' => 0,
        ];
    }
}
