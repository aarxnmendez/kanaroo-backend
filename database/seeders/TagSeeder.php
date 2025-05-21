<?php

namespace Database\Seeders;

use App\Models\Project; // Import Project model
use App\Models\Tag;     // Import Tag model
use App\Models\Item;    // Import Item model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB; // For attaching tags, if not using Eloquent relationships

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleTagNames = ['Urgent', 'Feature Request', 'Bug Fix', 'Documentation', 'Design Task', 'Refactor', 'Testing'];
        $projects = Project::all();

        if ($projects->isEmpty()) {
            // $this->command->info('No projects found, skipping TagSeeder.');
            return;
        }

        foreach ($projects as $project) {
            $projectTags = []; // To store tags created for this specific project

            // Create sample tags for the current project
            foreach ($sampleTagNames as $tagName) {
                $tag = Tag::factory()->create([
                    'project_id' => $project->id,
                    'name' => $tagName,
                    // Color will be handled by TagFactory
                ]);
                $projectTags[] = $tag->id; // Store the ID of the created tag
            }

            // Get items for the current project
            // This assumes items are linked to projects indirectly via sections.
            $projectItemIds = $project->sections()->with('items')->get()->pluck('items.*.id')->flatten()->unique()->toArray();
            
            if (empty($projectItemIds) || empty($projectTags)) {
                continue; // No items or no tags for this project, skip to next project
            }

            // Attach a random number of tags (1 to 3) to each item in the project
            $itemsInProject = Item::whereIn('id', $projectItemIds)->get();

            foreach ($itemsInProject as $item) {
                // Ensure there are tags to select from
                if (count($projectTags) > 0) {
                    $tagsToAttachCount = rand(1, min(3, count($projectTags))); // Attach 1 to 3 tags, or fewer if not enough tags
                    $randomTagIds = (array) array_rand(array_flip($projectTags), $tagsToAttachCount); // Get random tag IDs from this project's tags
                    
                    $item->tags()->attach($randomTagIds);
                }
            }
        }
    }
}
