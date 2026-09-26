<?php

namespace Database\Factories;

use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['National ID Card', 'Passport Copy', 'Academic Certificate', 'Experience Letter', 'Resume']),
            'file' => 'documents/' . fake()->uuid() . '.pdf',
            'expiry_date' => fake()->optional()->dateTimeBetween('+1 years', '+5 years')?->format('Y-m-d'),
        ];
    }
}
