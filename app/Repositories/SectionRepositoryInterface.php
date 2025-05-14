<?php

namespace App\Repositories;

use App\Models\Section;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface SectionRepositoryInterface
{
    /**
     * Get all sections for a specific project
     */
    public function getAllForProject(Project $project): Collection;

    /**
     * Create a new section
     */
    public function create(array $data, Project $project): Section;

    /**
     * Find a section by its ID
     */
    public function findById(int $id): ?Section;

    /**
     * Update a section
     */
    public function update(Section $section, array $data): Section;

    /**
     * Delete a section
     */
    public function delete(Section $section): bool;

    /**
     * Reorder sections in a project
     */
    public function reorder(Project $project, array $orderedIds): Collection;
}
