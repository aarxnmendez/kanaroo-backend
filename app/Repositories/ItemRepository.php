<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Section;
use App\Models\User; // For assigning creator (user_id)
use App\Models\Tag; // Added for tag validation
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth; // To get the authenticated user
use Illuminate\Support\Facades\DB; // For transactions, if needed for reorder
use Exception; // For error handling
use Illuminate\Support\Facades\Log; // For logging errors

class ItemRepository implements ItemRepositoryInterface
{
    /**
     * Get all items for a section.
     * Eager loads user, assignee, and tags. Orders by position.
     */
    public function getAllForSection(Section $section, array $filters = []): Collection
    {
        // Start with the base query from the section's own filtering logic
        $query = $section->filteredItems();

        // Apply additional dynamic filters from the $filters array
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            if (strtolower(strval($filters['assigned_to'])) === 'null' || $filters['assigned_to'] === 0) {
                $query->whereNull('assigned_to');
            } elseif (ctype_digit(strval($filters['assigned_to'])) && $filters['assigned_to'] > 0) {
                $query->where('assigned_to', (int)$filters['assigned_to']);
            }
        }

        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $tagIds = array_filter($filters['tags'], fn($id) => is_numeric($id) && $id > 0);
            if (!empty($tagIds)) {
                // Items that have ALL specified tags
                $query->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                }, '=', count($tagIds));
            }
        }

        // Add more dynamic filters here as needed (e.g., for date ranges independent of section's date filter)

        // Eager load common relationships
        $query->with(['assignee', 'tags', 'user']);

        // Apply final ordering
        $query->orderBy('position', 'asc');

        // Apply section's item limit (if set) AFTER all filters and ordering of the base set
        if ($section->item_limit && $section->item_limit > 0) {
            $query->limit($section->item_limit);
        }

        return $query->get();
    }

    /**
     * Create a new item in a section.
     * The creator (user_id) is the authenticated user.
     * Tags can be synced via 'tag_ids'.
     */
    public function create(array $data, Section $section): Item
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            if (!$user) {
                // Should be caught by auth middleware.
                throw new Exception('User not authenticated.');
            }

            $position = $section->items()->max('position') + 1;

            $item = $section->items()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'todo',
                'position' => $position,
                'user_id' => $user->id, // Item creator
                'assigned_to' => $data['assigned_to'] ?? null,
                // section_id is auto-handled by items() relationship
            ]);

            if (isset($data['tag_ids']) && is_array($data['tag_ids'])) {
                $this->syncTags($item, $data['tag_ids']);
            }

            // Load relations before returning
            return $item->load(['user', 'assignee', 'tags']);
        } catch (Exception $e) {
            Log::error('Error creating item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find an item by its ID.
     */
    public function findById(int $id): ?Item
    {
        try {
            // Find the item and load its relations
            return Item::with(['user', 'assignee', 'tags'])->find($id);
        } catch (Exception $e) {
            Log::error("Error finding item {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing item.
     * Tags can be synced via 'tag_ids'.
     * Returns fresh model with user, assignee, and tags loaded.
     */
    public function update(Item $item, array $data): Item
    {
        try {
            $item->update($data);

            if (array_key_exists('tag_ids', $data)) {
                // Sync tags. Null or empty array removes all tags.
                $this->syncTags($item, $data['tag_ids'] ?? []);
            }

            return $item->fresh(['user', 'assignee', 'tags']);
        } catch (Exception $e) {
            Log::error("Error updating item {$item->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an item.
     * Detaches tags before deletion.
     */
    public function delete(Item $item): bool
    {
        try {
            // Related data like comments might need explicit handling or rely on DB cascades.
            return $item->delete();
        } catch (Exception $e) {
            Log::error("Error deleting item {$item->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reorder items within a section.
     * Validates item IDs belong to the section.
     * Uses a DB transaction.
     * Returns reordered items with relations.
     */
    public function reorder(Section $section, array $orderedIds): Collection
    {
        // Validate all item IDs belong to the section.
        $itemsInSection = $section->items()->whereIn('id', $orderedIds)->pluck('id')->all();
        if (count($orderedIds) !== count($itemsInSection) || array_diff($orderedIds, $itemsInSection)) {
            throw new Exception('Invalid item IDs provided for reordering in this section.');
        }

        DB::beginTransaction();
        try {
            foreach ($orderedIds as $index => $itemId) {
                $position = $index + 1; // Typically 1-indexed position
                Item::where('id', $itemId)
                    ->where('section_id', $section->id)
                    ->update(['position' => $position]);
            }
            DB::commit();

            // Return reordered items, with relations.
            return $section->items()->whereIn('id', $orderedIds)->orderBy('position')->with(['user', 'assignee', 'tags'])->get();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error reordering items for section {$section->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync tags for an item.
     */
    public function syncTags(Item $item, array $tagIds): void
    {
        try {
            if (empty($tagIds)) {
                $item->tags()->sync([]);
                return;
            }

            // Validate that all provided tag IDs belong to the item's project
            $projectTags = Tag::where('project_id', $item->section->project_id)
                ->whereIn('id', $tagIds)
                ->pluck('id')
                ->all();

            $invalidTags = array_diff($tagIds, $projectTags);
            if (!empty($invalidTags)) {
                throw new Exception('Invalid tag(s) provided: IDs ' . implode(', ', $invalidTags) . ' do not belong to the project or do not exist.');
            }

            $item->tags()->sync($projectTags); // Use validated $projectTags
        } catch (Exception $e) {
            Log::error("Error syncing tags for item {$item->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
