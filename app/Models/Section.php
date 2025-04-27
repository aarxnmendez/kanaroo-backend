<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        } elseif ($this->filter_type === 'date') {
            // Implement date filters based on specific requirements
            // Example: due_date is today, this week, etc.
        }

        return $query->orderBy('position');
    }
}
