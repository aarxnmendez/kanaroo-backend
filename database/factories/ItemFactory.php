<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['todo', 'in_progress', 'done', 'blocked', 'archived'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return [
            'section_id' => Section::factory(),
            'user_id' => User::factory(), // Creator
            'assigned_to' => $this->faker->boolean(70) ? User::factory() : null, // 70% chance of being assigned
            'title' => $this->faker->catchPhrase(), // A short, catchy title
            'description' => $this->faker->optional()->paragraph(), // Optional paragraph for description
            'due_date' => $this->faker->optional()->dateTimeBetween('+1 day', '+1 month'), // Optional due date in the next month
            'position' => $this->faker->numberBetween(1, 20),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
        ];
    }
}
