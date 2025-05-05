<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    /**
     * Get all projects for a specific user with pagination
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new project
     */
    public function create(array $data, int $userId): Project;

    /**
     * Find a project by its ID
     */
    public function findById(int $id): ?Project;

    /**
     * Update a project
     */
    public function update(Project $project, array $data): Project;

    /**
     * Delete a project
     */
    public function delete(Project $project): bool;

    /**
     * Load standard relationships for a project
     */
    public function loadRelationships(Project $project): Project;
}
