<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    protected array $defaultSections = [
        ['name' => 'Pendiente', 'filter_value' => 'todo', 'filter_type' => 'status'],
        ['name' => 'En Progreso', 'filter_value' => 'in_progress', 'filter_type' => 'status'],
        ['name' => 'Hecho', 'filter_value' => 'done', 'filter_type' => 'status'],
        ['name' => 'Bloqueado', 'filter_value' => 'blocked', 'filter_type' => 'none'], // Auxiliary status
        ['name' => 'Archivado', 'filter_value' => 'archived', 'filter_type' => 'none'], // Auxiliary status
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $projects = Project::all();

        if ($projects->isEmpty()) {
            return;
        }

        foreach ($projects as $project) {
            // Only seed sections if the project doesn't have any yet.
            if ($project->sections()->exists()) {
                continue;
            }

            foreach ($this->defaultSections as $index => $sectionData) {
                Section::factory()->create([
                    'project_id' => $project->id,
                    'name' => $sectionData['name'],
                    'position' => $index + 1,
                    'filter_value' => $sectionData['filter_value'],
                    'filter_type' => $sectionData['filter_type'], // Use defined filter_type
                ]);
            }
        }
    }
}
