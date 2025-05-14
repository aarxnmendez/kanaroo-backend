<?php

namespace App\Policies;

use App\Models\Section;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SectionPolicy
{
    /**
     * Determine whether the user can create sections in a project.
     */
    public function create(User $user, Section $section): bool|Response
    {
        // User can create sections if they are the project owner or admin
        return $section->project->user_id === $user->id ||
            $section->project->users()->where('user_id', $user->id)->where('role', 'admin')->exists()
            ? true
            : Response::deny(__('errors.not_authorized_create'));
    }

    /**
     * Determine whether the user can view the section.
     */
    public function view(User $user, Section $section): bool|Response
    {
        // User can view sections if they have access to the project
        return $section->project->user_id === $user->id ||
            $section->project->users->contains($user->id)
            ? true
            : Response::deny(__('errors.not_authorized_view'));
    }

    /**
     * Determine whether the user can update the section.
     */
    public function update(User $user, Section $section): bool|Response
    {
        // User can update sections if they are the project owner or admin
        return $section->project->user_id === $user->id ||
            $section->project->users()->where('user_id', $user->id)->where('role', 'admin')->exists()
            ? true
            : Response::deny(__('errors.not_authorized_update'));
    }

    /**
     * Determine whether the user can delete the section.
     */
    public function delete(User $user, Section $section): bool|Response
    {
        // User can delete sections if they are the project owner or admin
        return $section->project->user_id === $user->id ||
            $section->project->users()->where('user_id', $user->id)->where('role', 'admin')->exists()
            ? true
            : Response::deny(__('errors.not_authorized_delete'));
    }

    /**
     * Determine whether the user can reorder sections.
     */
    public function reorder(User $user, Section $section): bool|Response
    {
        // User can reorder sections if they are the project owner or admin
        return $section->project->user_id === $user->id ||
            $section->project->users()->where('user_id', $user->id)->where('role', 'admin')->exists()
            ? true
            : Response::deny(__('errors.not_authorized_update'));
    }
}
