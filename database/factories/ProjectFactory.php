<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true), // e.g., "Efficient Project Management"
            'description' => $this->faker->sentence(), // e.g., "A short description for the project."
            'status' => $this->faker->randomElement(['active', 'archived', 'on_hold', 'completed']),
            'start_date' => $this->faker->optional(0.7)->date(),
            'end_date' => $this->faker->optional(0.7)->date(),
            'color' => $this->faker->optional(0.8)->hexColor(),
        ];
    }
}
