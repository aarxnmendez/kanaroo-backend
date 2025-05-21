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
     * Transfiere la propiedad de un proyecto a un nuevo usuario y actualiza los roles.
     *
     * @param Project $project El proyecto cuya propiedad se transferirá.
     * @param int $newOwnerId El ID del usuario que será el nuevo propietario.
     * @return Project El proyecto actualizado después de la transferencia de propiedad.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si el nuevo propietario no se encuentra.
     * @throws \Exception Si ocurre algún otro error durante la transacción.
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
