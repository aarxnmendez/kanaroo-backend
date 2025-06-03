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

    /**
     * Add a member to a project with a specific role.
     */
    public function addMember(Project $project, int $userId, string $role): bool;

    /**
     * Update a member's role in a project.
     */
    public function updateMemberRole(Project $project, int $userId, string $role): bool;

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, int $userId): bool;

    /**
     * Allows a user to leave a project.
     */
    public function userLeaveProject(Project $project, int $userId): bool;

    /**
     * Transfers ownership of a project to a new user and updates roles.
     *
     * @param Project $project The project whose ownership will be transferred.
     * @param int $newOwnerId The ID of the user who will be the new owner.
     * @return Project The updated project after ownership transfer.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the new owner is not found.
     * @throws \Exception If any other error occurs during the transaction.
     */
    public function transferOwnership(Project $project, int $newOwnerId): Project;

    /**
     * Get a paginated list of projects (id, name) for a specific user.
     * Orders by creation date descending.
     */
    public function getUserProjectList(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a project by its ID with all its related details (owner, members, sections with items, items with their user, assignee, and tags).
     * Sections and items are ordered by their 'position' attribute.
     */
    public function getProjectWithAllDetails(int $projectId): ?Project;
}
