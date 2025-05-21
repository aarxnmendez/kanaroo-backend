<?php

namespace Database\Seeders;

use App\Models\Item;    // Import Item model
use App\Models\Section; // Import Section model
use App\Models\User;    // Import User model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = Section::all();
        $users = User::all();

        if ($sections->isEmpty() || $users->isEmpty()) {
            // If there are no sections or users, we can't create items.
            // Optionally, log a message or handle this case as needed.
            // $this->command->info('No sections or users found, skipping ItemSeeder.');
            return;
        }

        foreach ($sections as $section) {
            // Create a random number of items for each section (e.g., 2 to 5)
            $numberOfItems = rand(2, 5); 

            for ($i = 0; $i < $numberOfItems; $i++) {
                $creator = $users->random();
                $assignee = null;

                // Decide if the item should have an assignee
                // For example, 70% chance of having an assignee
                if (rand(1, 10) <= 7) { 
                    $assignee = $users->random();
                }

                Item::factory()->create([
                    'section_id' => $section->id,
                    'user_id' => $creator->id,          // Creator of the item
                    'assigned_to' => $assignee ? $assignee->id : null, // Assignee (can be null)
                    'position' => $i + 1,               // Position within the section
                    // Title, description, due_date, priority, is_completed will be handled by ItemFactory
                ]);
            }
        }
    }
}
