<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;
use App\Models\Project; // Needed for context
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sections within a project.
     * This is typically controlled by ProjectPolicy::view.
     * The controller uses $this->authorize('view', $project).
     */
    // public function viewAny(User $user, Project $project): bool
    // {
    //     return $user->can('view', $project);
    // }

    /**
     * Determine whether the user can view the section.
     * True if the user can view the parent project.
     */
    public function view(User $user, Section $section): bool
    {
        return $user->can('view', $section->project);
    }

    /**
     * Determine whether the user can create sections.
     * This is typically controlled by ProjectPolicy::update (or a more specific one).
     * The controller uses $this->authorize('update', $project).
     */
    // public function create(User $user, Project $project): bool
    // {
    //     return $user->can('update', $project);
    // }

    /**
     * Determine whether the user can update the section.
     * True if the user can update the parent project (e.g., owner or admin).
     */
    public function update(User $user, Section $section): bool
    {
        return $user->can('update', $section->project);
    }

    /**
     * Determine whether the user can delete the section.
     * True if the user can update the parent project (e.g., owner or admin).
     * For more granular control, a dedicated 'deleteSections' permission could be on ProjectPolicy.
     */
    public function delete(User $user, Section $section): bool
    {
        // Assuming that if a user can update a project (owner/admin), they can delete its sections.
        // If only owner of project can delete sections, this should be $user->id === $section->project->user_id
        // or $user->can('delete', $section->project) if ProjectPolicy@delete is only for owner.
        // Current ProjectPolicy@update allows owner & admin. This seems reasonable for deleting sections.
        return $user->can('update', $section->project);
    }

    /**
     * Determine whether the user can reorder sections.
     * This is typically controlled by ProjectPolicy::update.
     * The controller uses $this->authorize('update', $project).
     */
    // public function reorder(User $user, Project $project): bool
    // {
    //     return $user->can('update', $project);
    // }
}
