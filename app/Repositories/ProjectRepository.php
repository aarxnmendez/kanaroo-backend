<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Section;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectRepository
{
    /**
     * Get all projects for a specific user with pagination.
     * Orders by creation date descending.
     * Includes projects owned by the user or where the user is a member.
     */
    public function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Project::where('user_id', $userId)
                ->orWhereHas('users', function (Builder $query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['user', 'users', 'sectionsCount', 'itemsCount']) // Include basic counts
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error("Error fetching projects for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new project.
     * Creator is automatically added as 'owner'.
     * Default sections are created with translatable names.
     */
    public function create(array $data, int $userId): Project
    {
        try {
            $project = DB::transaction(function () use ($data, $userId) {
                $projectData = $data;
                $projectData['user_id'] = $userId;

                $createdProject = Project::create($projectData);

                // Add creator as owner
                $createdProject->users()->attach($userId, ['role' => ProjectUser::ROLE_OWNER]);

                // Create default sections using translatable keys
                $defaultSections = [
                    ['name' => __('kanban.default_section_todo'),       'filter_type' => 'status', 'filter_value' => 'todo',        'position' => 1],
                    ['name' => __('kanban.default_section_in_progress'),'filter_type' => 'status', 'filter_value' => 'in_progress', 'position' => 2],
                    ['name' => __('kanban.default_section_done'),       'filter_type' => 'status', 'filter_value' => 'done',        'position' => 3],
                ];

                foreach ($defaultSections as $sectionData) {
                    Section::create(array_merge($sectionData, ['project_id' => $createdProject->id]));
                }
                
                return $createdProject;
            });

            return $this->loadRelationships($project);
        } catch (Exception $e) {
            Log::error("Error creating project for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a project by its ID. For simple lookups, consider a lighter version if needed.
     * This version fetches all details for consistency with typical project views.
     */
    public function findById(int $id): ?Project
    {
        try {
            return $this->getProjectWithAllDetails($id);
        } catch (Exception $e) {
            Log::error("Error finding project by ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a project by its ID with all nested relationships for Kanban view.
     * Includes: project owner, members, sections (ordered), items (ordered) with their user, assignee, tags.
     * Also includes counts for sections and items.
     */
    public function getProjectWithAllDetails(int $projectId): ?Project
    {
        try {
            return Project::with([
                'user', // Project owner
                'users', // Project members (pivot data like role included)
                'sections' => function ($query) {
                    $query->orderBy('position', 'asc')->with([
                        'items' => function ($query) {
                            $query->orderBy('position', 'asc')->with(['user', 'assignee', 'tags']); // Item creator, assignee, tags
                        },
                        'itemsCount' // Count of items in each section
                    ]);
                },
                'sectionsCount', // Total count of sections in the project
                'tags' // Tags directly associated with the project (distinct from item tags)
            ])->find($projectId);
        } catch (Exception $e) {
            Log::error("Error fetching project with all details for ID {$projectId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a project.
     */
    public function update(Project $project, array $data): Project
    {
        try {
            $project->update($data);
            return $this->loadRelationships($project->fresh()); // Use fresh() to get updated attributes before loading relations
        } catch (Exception $e) {
            Log::error("Error updating project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a project.
     * Relies on DB cascades or model events for related data cleanup.
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
     * Load standard relationships for a project model.
     * Includes: project owner, members, sections, and counts for sections and items.
     */
    public function loadRelationships(Project $project): Project
    {
        try {
            return $project->loadMissing(['user', 'users', 'sections', 'sectionsCount', 'itemsCount']);
        } catch (Exception $e) {
            Log::error("Error loading relationships for project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a member to a project.
     * Role validation should be handled by FormRequest or service layer.
     */
    public function addMember(Project $project, int $userId, string $role): bool
    {
        try {
            if ($project->users()->where('user_id', $userId)->exists()) {
                Log::info("Attempted to add existing member {$userId} to project {$project->id}. User already a member.");
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
     * Role validation should be handled by FormRequest or service layer.
     * Prevents changing project owner's role via this method; use transferOwnership instead.
     */
    public function updateMemberRole(Project $project, int $userId, string $role): bool
    {
        try {
            if (!$project->users()->where('user_id', $userId)->exists()) {
                Log::info("Attempted to update role for non-member {$userId} in project {$project->id}.");
                return false; // User not a member
            }

            // Prevent changing the project owner's role directly here.
            if ($project->user_id === $userId && $role !== ProjectUser::ROLE_OWNER) {
                Log::warning("Attempt to change project owner's role for user {$userId} in project {$project->id} via updateMemberRole. Denied.");
                return false; // Or throw specific exception
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
     * Prevents removing the project owner; use transferOwnership for that scenario.
     */
    public function removeMember(Project $project, int $userId): bool
    {
        try {
            // Prevent removing the project owner.
            if ($project->user_id === $userId) {
                Log::warning("Attempt to remove project owner {$userId} from project {$project->id} via removeMember. Denied.");
                return false; // Or throw specific exception
            }

            if (!$project->users()->where('user_id', $userId)->exists()) {
                Log::info("Attempted to remove non-member {$userId} from project {$project->id}.");
                return false; // User not a member, or already removed
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
     * Prevents the project owner from leaving; they must transfer ownership first.
     */
    public function userLeaveProject(Project $project, int $userId): bool
    {
        try {
            if ($project->user_id === $userId) {
                Log::warning("Project owner {$userId} attempted to leave project {$project->id} without transferring ownership. Denied.");
                return false; // Owner cannot leave, must transfer ownership
            }

            if (!$project->users()->where('user_id', $userId)->exists()) {
                Log::info("User {$userId} attempted to leave project {$project->id} but is not a member.");
                return false; // User not a member
            }

            $detachedCount = $project->users()->detach($userId);
            return $detachedCount > 0;
        } catch (Exception $e) {
            Log::error("Error allowing user {$userId} to leave project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transfers ownership of a project to a new user.
     * The new owner must be an existing member of the project.
     * The old owner's role is changed to 'admin'.
     */
    public function transferOwnership(Project $project, int $newOwnerId): Project
    {
        return DB::transaction(function () use ($project, $newOwnerId) {
            try {
                $newOwner = User::findOrFail($newOwnerId);
                $oldOwnerId = $project->user_id;

                // Ensure new owner is a member of the project (can be any role initially)
                if (!$project->users()->where('user_id', $newOwnerId)->exists()) {
                    Log::error("Cannot transfer ownership of project {$project->id}: New owner {$newOwnerId} is not a member.");
                    throw new Exception("New owner must be a member of the project to transfer ownership."); // More specific exception preferred
                }

                // Update project's owner_id
                $project->user_id = $newOwnerId;
                $project->save();

                // Update new owner's role to 'owner'
                $project->users()->updateExistingPivot($newOwnerId, ['role' => ProjectUser::ROLE_OWNER]);

                // Change old owner's role to 'admin' if they are different from the new owner
                if ($oldOwnerId !== $newOwnerId) {
                    if ($project->users()->where('user_id', $oldOwnerId)->exists()) {
                        $project->users()->updateExistingPivot($oldOwnerId, ['role' => ProjectUser::ROLE_ADMIN]);
                    } else {
                        // This case should ideally not happen if old owner was indeed the owner.
                        // If it does, it implies data inconsistency. Log it.
                        Log::warning("Old owner {$oldOwnerId} was not found in project_users pivot table during ownership transfer for project {$project->id}.");
                    }
                }
                
                return $this->loadRelationships($project->fresh());
            } catch (Exception $e) {
                Log::error("Error transferring ownership for project {$project->id} to user {$newOwnerId}: " . $e->getMessage());
                throw $e; // Re-throw to be caught by transaction rollback if DB::transaction is used directly
            }
        });
    }

    /**
     * Get a paginated list of projects (id, name, owner name) for a specific user for dropdowns or lists.
     * Orders by creation date descending.
     */
    public function getUserProjectList(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Project::select(['projects.id', 'projects.name', 'users.name as owner_name', 'projects.created_at'])
                ->join('users', 'users.id', '=', 'projects.user_id') // Join to get owner's name
                ->where('projects.user_id', $userId)
                ->orWhereHas('users', function (Builder $query) use ($userId) {
                    $query->where('users.id', $userId);
                })
                ->orderBy('projects.created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error("Error fetching user project list for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }
}

