<?php

namespace Database\Seeders;

use App\Models\Project; // Import Project model
use App\Models\User;    // Import User model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the specific Test User
        $testUser = User::where('email', 'test@example.com')->first();

        // Get some other users (e.g., the first 3 users who are not the Test User)
        // Ensure UserSeeder has run before this, or adjust query as needed
        $otherUsers = User::where('email', '!=', 'test@example.com')->take(3)->get();

        // Create projects for Test User if found
        if ($testUser) {
            Project::factory()->count(2)->create([
                'user_id' => $testUser->id,
                'description' => 'Sample project description for Test User.', // Optional: Add a default description
            ])->each(function ($project) use ($testUser) {
                $project->users()->attach($testUser->id, ['role' => 'owner']);
            });
        }

        // Create projects for other users
        if ($otherUsers->isNotEmpty()) {
            foreach ($otherUsers as $user) {
                $project = Project::factory()->create([
                    'user_id' => $user->id,
                    'description' => 'Another sample project description.', // Optional
                ]);
                $project->users()->attach($user->id, ['role' => 'owner']);
            }
        } else {
            // Fallback: if no other users found, create more projects for Test User or a generic new user
            // This ensures we always seed some projects if Test User exists
            if ($testUser) {
                 Project::factory()->count(3)->create([ // Create 3 more for test user
                    'user_id' => $testUser->id,
                    'description' => 'Additional sample project.',
                ])->each(function ($project) use ($testUser) {
                    $project->users()->attach($testUser->id, ['role' => 'owner']);
                });
            }
        }

        // If you want a fixed total number of projects regardless of user distribution,
        // you could adjust the logic, e.g., create a set number and then assign owners.
        // For now, this creates 2 for Test User + 1 for each of up to 3 other users (total 2-5 projects)
        // or up to 5 projects for Test User if no other users are found.
    }
}
