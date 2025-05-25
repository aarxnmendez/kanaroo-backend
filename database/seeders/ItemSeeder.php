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
            return;
        }

        $validItemStatuses = ['todo', 'in_progress', 'done', 'blocked', 'archived'];

        foreach ($sections as $section) {
            // Create a random number of items for each section (e.g., 2 to 5)
            $numberOfItems = rand(2, 5);

            $itemStatusForSection = $section->filter_value && in_array($section->filter_value, $validItemStatuses)
                ? $section->filter_value
                : 'todo';

            for ($i = 0; $i < $numberOfItems; $i++) {
                $creator = $users->random();
                $assignee = null;

                if (rand(1, 10) <= 7) {
                    $assignee = $users->random();
                }

                Item::factory()->create([
                    'section_id' => $section->id,
                    'user_id' => $creator->id,
                    'assigned_to' => $assignee ? $assignee->id : null,
                    'position' => $i + 1,
                    'status' => $itemStatusForSection,
                ]);
            }
        }
    }
}
