<?php

namespace Database\Seeders;

use App\Models\Project; // Import Project model
use App\Models\Section; // Import Section model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default section names
        $defaultSectionNames = ['To Do', 'In Progress', 'Done'];

        // Get all projects
        $projects = Project::all();

        foreach ($projects as $project) {
            foreach ($defaultSectionNames as $index => $sectionName) {
                Section::factory()->create([
                    'project_id' => $project->id,
                    'name' => $sectionName,
                    'position' => $index + 1, // Set position starting from 1
                    // 'description' => 'Default description for ' . $sectionName, // Optional
                    // 'filter_value' => strtolower(str_replace(' ', '_', $sectionName)), // Optional, if you use filter_value
                ]);
            }
        }
    }
}
