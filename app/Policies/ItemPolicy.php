<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use App\Models\Section; // Needed for viewAny and create contexts
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    /**
     * User can view items in a section if they can view the parent project.
     */
    public function viewAny(User $user, Section $section): bool
    {
        return $user->can('view', $section->project);
    }

    /**
     * User can view an item if they can view the parent project.
     */
    public function view(User $user, Item $item): bool
    {
        return $user->can('view', $item->section->project);
    }

    /**
     * User can create items in a section if they can update the parent project.
     */
    public function create(User $user, Section $section): bool
    {
        return $user->can('update', $section->project);
    }

    /**
     * User can update item if they created it or can update the parent project.
     */
    public function update(User $user, Item $item): bool
    {
        return $user->id === $item->user_id || $user->can('update', $item->section->project);
    }

    /**
     * User can delete item if they created it or can update the parent project.
     */
    public function delete(User $user, Item $item): bool
    {
        // Using 'update' on project as a proxy for delete rights on child elements.
        // For more granular control, a distinct 'deleteItems' or 'manageProjectContents' 
        // permission could be used on the project policy.
        return $user->id === $item->user_id || $user->can('update', $item->section->project);
    }

    // Soft delete methods restore() and forceDelete() are not included
    // as Item model does not use SoftDeletes trait.
    // Reorder authorization is handled by SectionPolicy::update in ItemController.
}
