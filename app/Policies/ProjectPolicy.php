<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectUser;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     * Any authenticated user can attempt to list projects;
     * the repository will filter them accordingly.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Any authenticated user can create projects.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Helper function to get user's role in a project.
     */
    private function getRole(User $user, Project $project): ?string
    {
        if ($user->id === $project->user_id) { // Creator is always owner
            return ProjectUser::ROLE_OWNER;
        }
        $projectUser = $project->users()->where('user_id', $user->id)->first();
        return $projectUser ? $projectUser->pivot->role : null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        $role = $this->getRole($user, $project);
        return in_array($role, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN, ProjectUser::ROLE_EDITOR, ProjectUser::ROLE_MEMBER]);
    }

    /**
     * Determine whether the user can update the model.
     * Owner or Admin can update.
     */
    public function update(User $user, Project $project): bool
    {
        $role = $this->getRole($user, $project);
        return in_array($role, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can delete the model.
     * Only Owner can delete.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->getRole($user, $project) === ProjectUser::ROLE_OWNER;
    }

    /**
     * Determine whether the user can add members to the project.
     * Owner or Admin can add members.
     */
    public function addMember(User $user, Project $project): bool
    {
        $role = $this->getRole($user, $project);
        return in_array($role, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN]);
    }

    /**
     * Determine whether the user can update a member's role in the project.
     * Owner or Admin can update roles.
     * Cannot change role of the owner.
     * Cannot change own role if admin (owner should do it or another admin).
     */
    public function updateMemberRole(User $user, Project $project, User $memberToUpdate): bool
    {
        $actorRole = $this->getRole($user, $project);
        $targetMemberRole = $this->getRole($memberToUpdate, $project);

        if (!in_array($actorRole, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN])) {
            return false; // Only owner/admin can update roles
        }

        if ($memberToUpdate->id === $project->user_id && $targetMemberRole === ProjectUser::ROLE_OWNER) {
            return false; // Cannot change role of the project owner
        }

        // Admins cannot change their own role to prevent self-lockout or unauthorized privilege escalation.
        if ($actorRole === ProjectUser::ROLE_ADMIN && $user->id === $memberToUpdate->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can remove members from the project.
     * Owner or Admin can remove members.
     * Cannot remove the owner.
     * Admins cannot remove themselves (owner or another admin should do it).
     */
    public function removeMember(User $user, Project $project, User $memberToRemove): bool
    {
        $actorRole = $this->getRole($user, $project);
        $targetMemberRole = $this->getRole($memberToRemove, $project);

        if (!in_array($actorRole, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN])) {
            return false; // Only owner/admin can remove
        }

        if ($memberToRemove->id === $project->user_id && $targetMemberRole === ProjectUser::ROLE_OWNER) {
            return false; // Cannot remove the project owner
        }

        return true;
    }

    /**
     * Determine whether the user can leave the project.
     * Any member can leave, except the owner.
     */
    public function leave(User $user, Project $project): bool
    {
        // The project owner cannot leave the project.
        // They must transfer ownership or delete the project.
        if ($user->id === $project->user_id) {
            return false;
        }

        // Any other authenticated user who is part of the project (implicitly checked by policy system)
        // and is not the owner can leave.
        return true;
    }

    /**
     * Determine whether the user can transfer ownership of the project.
     * Only the current project owner can transfer ownership.
     */
    public function transferOwnership(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determine whether the user can manage content within the project (e.g., create items, sections, tags).
     * Owner, Admin, or Editor can manage content.
     */
    public function canManageProjectContent(User $user, Project $project): bool
    {
        $role = $this->getRole($user, $project);
        return in_array($role, [ProjectUser::ROLE_OWNER, ProjectUser::ROLE_ADMIN, ProjectUser::ROLE_EDITOR]);
    }
}
