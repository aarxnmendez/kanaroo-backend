<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        // Any authenticated user can create projects
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool|Response
    {
        // Allow viewing if the user is the owner or a collaborator
        return $user->id === $project->user_id || $project->users->contains($user->id)
            ? true
            : Response::deny(__('errors.not_authorized_view'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool|Response
    {
        // Only the owner can update
        return $user->id === $project->user_id
            ? true
            : Response::deny(__('errors.not_authorized_update'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool|Response
    {
        // Only the owner can delete
        return $user->id === $project->user_id
            ? true
            : Response::deny(__('errors.not_authorized_delete'));
    }
}
