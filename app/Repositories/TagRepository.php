<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class TagRepository implements TagRepositoryInterface
{
    /**
     * Get all tags for a specific project, ordered by name.
     */
    public function getAllForProject(Project $project): Collection
    {
        try {
            return $project->tags()->orderBy('name')->get();
        } catch (Exception $e) {
            Log::error("Error fetching tags for project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new tag for a specific project.
     */
    public function create(array $data, Project $project): Tag
    {
        try {
            return $project->tags()->create([
                'name' => $data['name'],
                'color' => $data['color'] ?? '#3b82f6', // Default color
            ]);
        } catch (Exception $e) {
            Log::error("Error creating tag for project {$project->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a tag by its ID.
     */
    public function findById(int $id): ?Tag
    {
        try {
            return Tag::find($id);
        } catch (Exception $e) {
            Log::error("Error finding tag {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing tag.
     */
    public function update(Tag $tag, array $data): Tag
    {
        try {
            $tag->update($data);
            return $tag;
        } catch (Exception $e) {
            Log::error("Error updating tag {$tag->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a tag.
     * Relies on DB cascade for item_tag pivot table cleanup.
     */
    public function delete(Tag $tag): bool
    {
        try {
            // $tag->items()->detach(); // Redundant if item_tag.tag_id has ON DELETE CASCADE
            return $tag->delete();
        } catch (Exception $e) {
            Log::error("Error deleting tag {$tag->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
