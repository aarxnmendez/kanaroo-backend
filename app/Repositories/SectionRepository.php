<?php

namespace App\Repositories;

use App\Models\Section;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SectionRepository implements SectionRepositoryInterface
{
    /**
     * Get all sections for a specific project, ordered by position and with item counts.
     */
    public function getAllForProject(Project $project): Collection
    {
        try {
            return $project->sections()
                ->withCount('items')
                ->orderBy('position')
                ->get();
        } catch (Exception $e) {
            Log::error('Error fetching project sections: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new section for a given project.
     * The position is automatically determined.
     */
    public function create(array $data, Project $project): Section
    {
        try {
            $position = $project->sections()->max('position') + 1;

            return $project->sections()->create([
                'name' => $data['name'],
                'position' => $position,
                'filter_type' => $data['filter_type'] ?? 'none',
                'filter_value' => $data['filter_value'] ?? null,
                'item_limit' => $data['item_limit'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Error creating section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a section by its ID, including the count of its items.
     */
    public function findById(int $id): ?Section
    {
        try {
            return Section::withCount('items')->find($id);
        } catch (Exception $e) {
            Log::error('Error finding section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing section.
     */
    public function update(Section $section, array $data): Section
    {
        try {
            $section->update($data);
            return $section;
        } catch (Exception $e) {
            Log::error('Error updating section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a section.
     */
    public function delete(Section $section): bool
    {
        try {
            return $section->delete();
        } catch (Exception $e) {
            Log::error('Error deleting section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reorder sections within a project based on an array of ordered section IDs.
     * Uses a database transaction to ensure atomicity.
     * Returns the reordered collection of sections with item counts.
     */
    public function reorder(Project $project, array $orderedIds): Collection
    {
        try {
            // Verify all sections belong to the project first, outside the transaction
            // as this is a read operation and doesn't need to be rolled back.
            $sections = $project->sections()->whereIn('id', $orderedIds)->get();

            if (count($sections) !== count($orderedIds)) {
                throw new Exception('Invalid section IDs provided for reordering or some sections do not belong to the project.');
            }

            // Use a database transaction for updating positions
            DB::transaction(function () use ($project, $orderedIds) {
                foreach ($orderedIds as $position => $id) {
                    $project->sections()
                        ->where('id', $id)
                        ->update(['position' => $position + 1]);
                }
            });

            // Reload sections with item counts after reordering
            return $project->sections()
                ->withCount('items')
                ->orderBy('position')
                ->get();
        } catch (Exception $e) {
            Log::error('Error reordering sections: ' . $e->getMessage());
            throw $e;
        }
    }
}
