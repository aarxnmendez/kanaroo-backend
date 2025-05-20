<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JsonException;
use Illuminate\Support\Facades\Log;

class Section extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'position',
        'filter_type',
        'filter_value',
        'item_limit',
        'project_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filter_value' => 'array', // For JSON filter criteria
        'item_limit' => 'integer',
    ];

    /**
     * Get the project that owns the section.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the items for the section.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get filtered items based on section's filter configuration.
     * This method applies filters based on the section's `filter_type` and `filter_value`.
     * It supports filtering by status, priority, assigned user, tag, and various date conditions.
     */
    public function filteredItems()
    {
        $query = $this->items();

        if ($this->filter_type === 'status' && $this->filter_value) {
            $query->where('status', $this->filter_value);
        } elseif ($this->filter_type === 'priority' && $this->filter_value) {
            $query->where('priority', $this->filter_value);
        } elseif ($this->filter_type === 'assigned_to' && $this->filter_value) {
            $query->where('assigned_to', $this->filter_value);
        } elseif ($this->filter_type === 'tag' && $this->filter_value) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->filter_value);
            });
        } elseif ($this->filter_type === 'date' && $this->filter_value) {
            try {
                $dateFilters = $this->filter_value; // Already an array due to $casts

                if ($this->filter_type === 'date' && is_array($dateFilters)) {
                    if (isset($dateFilters['due_on'])) {
                        $query->whereDate('due_date', $dateFilters['due_on']);
                    } elseif (isset($dateFilters['due_between']['start']) && isset($dateFilters['due_between']['end'])) {
                        $query->whereBetween('due_date', [$dateFilters['due_between']['start'], $dateFilters['due_between']['end']]);
                    } elseif (isset($dateFilters['due_after'])) {
                        $query->whereDate('due_date', '>=', $dateFilters['due_after']);
                    } elseif (isset($dateFilters['due_before'])) {
                        $query->whereDate('due_date', '<=', $dateFilters['due_before']);
                    } elseif (isset($dateFilters['is_null']) && $dateFilters['is_null'] === true) {
                        $query->whereNull('due_date');
                    } elseif (isset($dateFilters['is_not_null']) && $dateFilters['is_not_null'] === true) {
                        $query->whereNotNull('due_date');
                    } elseif (isset($dateFilters['overdue']) && $dateFilters['overdue'] === true) {
                        $query->whereDate('due_date', '<', now()->toDateString())
                            ->whereNotIn('status', ['done', 'archived']);
                    }
                }
            } catch (JsonException $e) {
                Log::error("Error processing date filter for section {$this->id}: " . $e->getMessage());
            }
        }

        // DO NOT apply limit or order here if repo will handle it
        // if ($this->item_limit && $this->item_limit > 0) {
        //     $query->limit($this->item_limit);
        // }
        // return $query->orderBy('position'); 

        return $query; // Return the Eloquent Builder
    }
}
