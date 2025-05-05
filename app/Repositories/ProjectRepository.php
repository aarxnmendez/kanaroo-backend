<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * Get all projects for a specific user with pagination
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Project::where('user_id', $userId)
                ->orWhereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['users'])
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error fetching user projects: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new project
     */
    public function create(array $data, int $userId): Project
    {
        try {
            $project = Project::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'user_id' => $userId,
            ]);

            // Add user as a member with 'owner' role
            $project->users()->attach($userId, ['role' => 'owner']);

            return $this->loadRelationships($project);
        } catch (Exception $e) {
            Log::error('Error creating project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a project by its ID
     */
    public function findById(int $id): ?Project
    {
        try {
            $project = Project::find($id);

            if ($project) {
                return $this->loadRelationships($project);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error finding project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a project
     */
    public function update(Project $project, array $data): Project
    {
        try {
            $project->update($data);
            return $this->loadRelationships($project);
        } catch (Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a project
     */
    public function delete(Project $project): bool
    {
        try {
            return $project->delete();
        } catch (Exception $e) {
            Log::error('Error deleting project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load standard relationships for a project
     */
    public function loadRelationships(Project $project): Project
    {
        try {
            return $project->load(['users', 'user']);
        } catch (Exception $e) {
            Log::error('Error loading project relationships: ' . $e->getMessage());
            throw $e;
        }
    }
}
