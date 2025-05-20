<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tags within a project.
     * User can view tags if they can view the project.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->can('view', $project);
    }

    /**
     * Determine whether the user can view the tag.
     * User can view a tag if they can view its project.
     */
    public function view(User $user, Tag $tag): bool
    {
        return $user->can('view', $tag->project);
    }

    /**
     * Determine whether the user can create tags within a project.
     * User can create tags if they can update the project (owner/admin).
     */
    public function create(User $user, Project $project): bool
    {
        return $user->can('update', $project);
    }

    /**
     * Determine whether the user can update the tag.
     * User can update a tag if they can update its project (owner/admin).
     */
    public function update(User $user, Tag $tag): bool
    {
        return $user->can('update', $tag->project);
    }

    /**
     * Determine whether the user can delete the tag.
     * User can delete a tag if they can update its project (owner/admin).
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user->can('update', $tag->project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return false;
    }
}
