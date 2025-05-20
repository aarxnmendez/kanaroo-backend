<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectUser; // Added for ROLE_ constants
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Exception;

class ProjectRepository implements ProjectRepositoryInterface
{
    /**
     * Get all projects for a specific user with pagination.
     * Orders by creation date descending.
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Project::where('user_id', $userId)
                ->orWhereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['users', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error fetching user projects: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new project.
     * Creator is automatically added as 'owner'.
     */
    public function create(array $data, int $userId): Project
    {
        try {
            $project = Project::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'user_id' => $userId,
            ]);

            $project->users()->attach($userId, ['role' => ProjectUser::ROLE_OWNER]);
            return $this->loadRelationships($project);
        } catch (Exception $e) {
            Log::error('Error creating project: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a project by its ID, with standard relationships loaded.
     */
    public function findById(int $id): ?Project
    {
        try {
            $project = Project::find($id);
            return $project ? $this->loadRelationships($project) : null;
        } catch (Exception $e) {
            Log::error("Error finding project {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a project, with standard relationships reloaded.
     */
    public function update(Project $project, array $data): Project
    {
        try {
            $project->update($data);
            return $this->loadRelationships($project);
        } catch (Exception $e) {
            Log::error("Error updating project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a project.
     * Relies on DB cascades or model events for related data.
     */
    public function delete(Project $project): bool
    {
        try {
            return $project->delete();
        } catch (Exception $e) {
            Log::error("Error deleting project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load standard relationships for a project (creator, members, sections, counts).
     */
    public function loadRelationships(Project $project): Project
    {
        try {
            return $project->loadMissing(['user', 'users', 'sections'])
                ->loadCount(['sections', 'items']);
        } catch (Exception $e) {
            Log::error("Error loading relationships for project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a member to a project.
     * Role validation should be handled by FormRequest.
     */
    public function addMember(Project $project, int $userId, string $role): bool
    {
        try {
            if ($project->users()->where('user_id', $userId)->exists()) {
                Log::info("Attempted to add existing member {$userId} to project {$project->id}.");
                return false; // User is already a member
            }
            $project->users()->attach($userId, ['role' => $role]);
            return true;
        } catch (Exception $e) {
            Log::error("Error adding member {$userId} to project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a member's role in a project.
     * Role validation should be handled by FormRequest.
     * Prevents changing owner's role via this method.
     */
    public function updateMemberRole(Project $project, int $userId, string $role): bool
    {
        try {
            if (!$project->users()->where('user_id', $userId)->exists()) {
                Log::info("Attempted to update role for non-member {$userId} in project {$project->id}.");
                return false; // User not a member
            }

            if ($project->user_id === $userId && $role !== ProjectUser::ROLE_OWNER) {
                Log::warning("Attempt to change project owner's role via repository for project {$project->id}, user {$userId}");
                return false;
            }
            $project->users()->updateExistingPivot($userId, ['role' => $role]);
            return true;
        } catch (Exception $e) {
            Log::error("Error updating member role for user {$userId} in project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove a member from a project.
     * Prevents removing the project owner.
     */
    public function removeMember(Project $project, int $userId): bool
    {
        try {
            if ($project->user_id === $userId) {
                Log::warning("Attempt to remove project owner {$userId} from project {$project->id} via repository.");
                return false;
            }
            $detachedCount = $project->users()->detach($userId);
            return $detachedCount > 0;
        } catch (Exception $e) {
            Log::error("Error removing member {$userId} from project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Allows a user to leave a project.
     * Detaches the user from the project's 'users' relationship.
     */
    public function userLeaveProject(Project $project, int $userId): bool
    {
        try {
            // The project owner cannot be detached via this method.
            // This should ideally be caught by policy, but as a safeguard:
            if ($project->user_id === $userId) {
                Log::warning("Attempt to detach project owner {$userId} from project {$project->id} via userLeaveProject method.");
                return false;
            }
            
            // The detach method returns the number of detached records.
            // If > 0, it means the user was successfully detached.
            $detachedCount = $project->users()->detach($userId);
            return $detachedCount > 0;
        } catch (Exception $e) {
            Log::error("Error detaching user {$userId} from project {$project->id}: " . $e->getMessage());
            throw $e; // Or return false if you don't want to rethrow
        }
    }
}
