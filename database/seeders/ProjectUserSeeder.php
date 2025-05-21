<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB; // To use DB::table for pivot if not using Eloquent attach

class ProjectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        // Define possible roles for project members (excluding 'owner' which is implicit for project creator)
        // Verify these roles against your application's actual roles (e.g., from an Enum or validation)
        $roles = ['admin', 'editor', 'member'];

        if ($projects->isEmpty() || $users->count() < 2) { // Need at least one project and more than one user to add others
            // $this->command->info('Not enough projects or users to seed ProjectUser table extensively.');
            return;
        }

        foreach ($projects as $project) {
            $projectOwnerId = $project->user_id;

            // Get users who are not the project owner
            $potentialMembers = $users->where('id', '!=', $projectOwnerId);

            if ($potentialMembers->isEmpty()) {
                continue; // No other users to add as members to this project
            }

            // Decide how many additional members to add (e.g., 0 to 3)
            $numberOfMembersToAdd = rand(0, min(3, $potentialMembers->count()));

            if ($numberOfMembersToAdd === 0) {
                continue; // No additional members for this project
            }

            // Select random users to be members
            $membersToAdd = $potentialMembers->random($numberOfMembersToAdd);
            // If random returns a single model when $numberOfMembersToAdd is 1, ensure it's a collection
            if ($numberOfMembersToAdd === 1 && !$membersToAdd instanceof \Illuminate\Database\Eloquent\Collection) {
                $membersToAdd = collect([$membersToAdd]);
            }


            foreach ($membersToAdd as $member) {
                // Check if the user is already a member of the project to avoid duplicates if attach doesn't handle it
                // Eloquent's attach method typically handles duplicates gracefully if the pivot table has a unique constraint on (project_id, user_id)
                // Or, you can use syncWithoutDetaching or updateExistingPivot
                
                // For simplicity, we assume attach will handle or we rely on DB constraints.
                // If not, a check like: if (!$project->users()->where('user_id', $member->id)->exists())
                
                $project->users()->attach($member->id, ['role' => $roles[array_rand($roles)]]);
            }
        }
    }
}
