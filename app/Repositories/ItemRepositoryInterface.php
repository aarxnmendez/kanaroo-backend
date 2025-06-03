<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    /**
     * Get all items for a specific section.
     *
     * @param  Section $section
     * @param  array $filters // Optional filters (e.g., for status, priority, not just section's default)
     * @return Collection<int, Item>
     */
    public function getAllForSection(Section $section, array $filters = []): Collection;

    /**
     * Create a new item for a specific section.
     *
     * @param  array $data
     * @param  Section $section
     * @return Item
     */
    public function create(array $data, Section $section): Item;

    /**
     * Find an item by its ID.
     *
     * @param  int $id
     * @return Item|null
     */
    public function findById(int $id): ?Item;

    /**
     * Update an existing item.
     *
     * @param  Item $item
     * @param  array $data
     * @return Item
     */
    public function update(Item $item, array $data): Item;

    /**
     * Delete an item.
     *
     * @param  Item $item
     * @return bool
     */
    public function delete(Item $item): bool;

    /**
     * Reorder items within a section.
     *
     * @param  Section $section
     * @param  array $orderedIds
     * @return Collection<int, Item>
     */
    public function reorder(Section $section, array $orderedIds): Collection;

    /**
     * Attach tags to an item.
     *
     * @param  Item $item
     * @param  array $tagIds
     * @return void
     */
    public function syncTags(Item $item, array $tagIds): void;

    /**
     * Get filtered and paginated items for a specific project.
     *
     * @param  Project $project
     * @param  Request $request // Contains query parameters for filtering and sorting
     * @return LengthAwarePaginator<Item>
     */
    public function getFilteredItemsForProject(Project $project, Request $request): LengthAwarePaginator;
}
