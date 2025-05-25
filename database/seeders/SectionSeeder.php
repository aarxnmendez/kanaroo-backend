<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSections = [
            ['name' => 'Pendiente', 'filter_value' => 'todo'],
            ['name' => 'En Progreso', 'filter_value' => 'in_progress'],
            ['name' => 'Hecho', 'filter_value' => 'done'],
            ['name' => 'Bloqueado', 'filter_value' => 'blocked'],
            ['name' => 'Archivado', 'filter_value' => 'archived'],
        ];

        $projects = Project::all();

        if ($projects->isEmpty()) {
            return;
        }

        foreach ($projects as $project) {
            foreach ($defaultSections as $index => $sectionData) {
                Section::factory()->create([
                    'project_id' => $project->id,
                    'name' => $sectionData['name'],
                    'position' => $index + 1,
                    'filter_value' => $sectionData['filter_value'],
                ]);
            }
        }
    }
}
