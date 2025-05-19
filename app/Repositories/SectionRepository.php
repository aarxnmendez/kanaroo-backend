<?php

namespace App\Repositories;

use App\Models\Section;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class SectionRepository implements SectionRepositoryInterface
{
    /**
     * Get all sections for a specific project.
     * Sections are ordered by position and include filtered/limited items and total item counts.
     * @param Project $project The project for which to retrieve sections.
     * @return Collection A collection of Section models.
     */
    public function getAllForProject(Project $project): Collection
    {
        try {
            $sections = $project->sections()
                ->withCount('items') // Total items count before section-specific filters
                ->with(['items' => function ($query) {
                    $query->orderBy('position', 'asc')->with('tags');
                }])
                ->orderBy('position')
                ->get();

            $sections->each(function ($section) {
                $filteredAndLimitedItems = $this->applySectionFiltersToCollection($section, new Collection($section->items ?? []));
                $section->setRelation('items', $filteredAndLimitedItems);
            });
            return $sections;
        } catch (Exception $e) {
            Log::error('Error fetching project sections: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new section for a given project.
     * Position is automatically determined. Includes filtered/limited items.
     * @param array $data Data for creating the section.
     * @param Project $project The parent project.
     * @return Section The newly created Section model.
     */
    public function create(array $data, Project $project): Section
    {
        try {
            $position = $project->sections()->max('position') + 1;

            $section = $project->sections()->create([
                'name' => $data['name'],
                'position' => $position,
                'filter_type' => $data['filter_type'] ?? 'none',
                'filter_value' => $data['filter_value'] ?? null,
                'item_limit' => $data['item_limit'] ?? null,
            ]);
            $section->loadCount('items');

            $section->load(['items' => function ($query) {
                $query->orderBy('position', 'asc')->with('tags');
            }]);
            $filteredAndLimitedItems = $this->applySectionFiltersToCollection($section, new Collection($section->items ?? []));
            $section->setRelation('items', $filteredAndLimitedItems);

            return $section;
        } catch (Exception $e) {
            Log::error('Error creating section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a section by its ID.
     * Includes filtered/limited items and total item count.
     * @param int $id The ID of the section to find.
     * @return Section|null The found Section model or null if not found.
     */
    public function findById(int $id): ?Section
    {
        try {
            $section = Section::withCount('items')
                ->with(['items' => function ($query) {
                    $query->orderBy('position', 'asc')->with('tags');
                }])
                ->find($id);
            if ($section) {
                $filteredAndLimitedItems = $this->applySectionFiltersToCollection($section, new Collection($section->items ?? []));
                $section->setRelation('items', $filteredAndLimitedItems);
            }
            return $section;
        } catch (Exception $e) {
            Log::error('Error finding section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing section.
     * Includes reloaded filtered/limited items and total item count.
     * @param Section $section The section to update.
     * @param array $data Data for updating the section.
     * @return Section The updated Section model.
     */
    public function update(Section $section, array $data): Section
    {
        try {
            $section->update($data);
            $section->loadCount('items');

            $section->load(['items' => function ($query) {
                $query->orderBy('position', 'asc')->with('tags');
            }]);
            $filteredAndLimitedItems = $this->applySectionFiltersToCollection($section, new Collection($section->items ?? []));
            $section->setRelation('items', $filteredAndLimitedItems);
            return $section;
        } catch (Exception $e) {
            Log::error('Error updating section: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a section.
     * @param Section $section The section to delete.
     * @return bool True on success, false on failure.
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
     * Reorder sections within a project.
     * Uses a database transaction. Returns reordered sections with filtered/limited items.
     * @param Project $project The project whose sections are to be reordered.
     * @param array $orderedIds An array of section IDs in the new desired order.
     * @return Collection The reordered collection of Section models.
     * @throws Exception If invalid section IDs are provided.
     */
    public function reorder(Project $project, array $orderedIds): Collection
    {
        try {
            $sectionsInProject = $project->sections()->whereIn('id', $orderedIds)->get();

            if (count($sectionsInProject) !== count($orderedIds)) {
                throw new Exception('Invalid section IDs provided for reordering or some sections do not belong to the project.');
            }

            DB::transaction(function () use ($project, $orderedIds) {
                foreach ($orderedIds as $position => $id) {
                    $project->sections()
                        ->where('id', $id)
                        ->update(['position' => $position + 1]);
                }
            });

            $reorderedSections = $project->sections()
                ->withCount('items')
                ->with(['items' => function ($query) {
                    $query->orderBy('position', 'asc')->with('tags');
                }])
                ->orderBy('position')
                ->get();

            $reorderedSections->each(function ($section) {
                $filteredAndLimitedItems = $this->applySectionFiltersToCollection($section, new Collection($section->items ?? []));
                $section->setRelation('items', $filteredAndLimitedItems);
            });

            return $reorderedSections;
        } catch (Exception $e) {
            Log::error('Error reordering sections: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Applies section-specific filters and limits to a collection of items.
     * This method operates on an already loaded collection of items for a section.
     * @param Section $section The section whose filter rules to apply.
     * @param Collection $itemsCollection The base collection of items for the section.
     * @return Collection The filtered and limited collection of items.
     */
    protected function applySectionFiltersToCollection(Section $section, Collection $itemsCollection): Collection
    {
        $processedItems = $itemsCollection;

        if ($section->filter_type === 'status' && !empty($section->filter_value)) {
            $processedItems = $processedItems->where('status', $section->filter_value);
        } elseif ($section->filter_type === 'priority' && !empty($section->filter_value)) {
            $processedItems = $processedItems->where('priority', $section->filter_value);
        } elseif ($section->filter_type === 'assigned_to' && !empty($section->filter_value)) {
            $processedItems = $processedItems->where('assigned_to', $section->filter_value);
        } elseif ($section->filter_type === 'tag' && !empty($section->filter_value)) {
            $tagIdToFilter = $section->filter_value; // Assumes filter_value is a single ID for tags.
            if (!is_array($tagIdToFilter)) {
                $processedItems = $processedItems->filter(function ($item) use ($tagIdToFilter) {
                    return $item->tags && $item->tags->contains('id', $tagIdToFilter);
                });
            }
        } elseif ($section->filter_type === 'date' && !empty($section->filter_value) && is_array($section->filter_value)) {
            $dateFilters = $section->filter_value;

            if (isset($dateFilters['due_on'])) {
                $processedItems = $processedItems->filter(function ($item) use ($dateFilters) {
                    return $item->due_date && ($item->due_date instanceof Carbon ? $item->due_date->toDateString() : $item->due_date) === $dateFilters['due_on'];
                });
            } elseif (isset($dateFilters['due_between']['start']) && isset($dateFilters['due_between']['end'])) {
                $start = Carbon::parse($dateFilters['due_between']['start']);
                $end = Carbon::parse($dateFilters['due_between']['end']);
                $processedItems = $processedItems->filter(function ($item) use ($start, $end) {
                    return $item->due_date && ($item->due_date instanceof Carbon ? $item->due_date : Carbon::parse($item->due_date))->betweenIncluded($start, $end);
                });
            } elseif (isset($dateFilters['due_after'])) {
                $date = Carbon::parse($dateFilters['due_after']);
                $processedItems = $processedItems->filter(function ($item) use ($date) {
                    return $item->due_date && ($item->due_date instanceof Carbon ? $item->due_date : Carbon::parse($item->due_date))->gte($date);
                });
            } elseif (isset($dateFilters['due_before'])) {
                $date = Carbon::parse($dateFilters['due_before']);
                $processedItems = $processedItems->filter(function ($item) use ($date) {
                    return $item->due_date && ($item->due_date instanceof Carbon ? $item->due_date : Carbon::parse($item->due_date))->lte($date);
                });
            } elseif (isset($dateFilters['is_null']) && $dateFilters['is_null'] === true) {
                $processedItems = $processedItems->whereNull('due_date');
            } elseif (isset($dateFilters['is_not_null']) && $dateFilters['is_not_null'] === true) {
                $processedItems = $processedItems->whereNotNull('due_date');
            } elseif (isset($dateFilters['overdue']) && $dateFilters['overdue'] === true) {
                $today = Carbon::today();
                $processedItems = $processedItems->filter(function ($item) use ($today) {
                    return $item->due_date && ($item->due_date instanceof Carbon ? $item->due_date : Carbon::parse($item->due_date))->lt($today)
                        && !in_array($item->status, ['done', 'archived']);
                });
            }
        }

        // Base item ordering by 'position' is handled in the initial eager load.

        if ($section->item_limit && $section->item_limit > 0) {
            $processedItems = $processedItems->take($section->item_limit);
        }

        // Ensure collection keys are reset if filter() was used, which preserves keys.
        return new Collection($processedItems->values());
    }
}
