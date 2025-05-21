<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Section::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->words(2, true), // e.g., "Pending Review"
            'position' => $this->faker->numberBetween(1, 10), // A random position
            'filter_type' => 'none', // Default to 'none' as per migration, can be overridden
            'filter_value' => null, // Default to null
            'item_limit' => null,  // Default to null
        ];
    }
}
