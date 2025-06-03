<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ItemStatus;
use App\Enums\ItemPriority;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'position',
        'status',
        'priority',
        'section_id',
        'user_id',
        'assigned_to'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'status' => ItemStatus::class,
        'priority' => ItemPriority::class,
    ];

    /**
     * Get the section that contains the item.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the user who created the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to the item.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the tags associated with the item.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Scope a query to only include items due before or on a given date.
     */
    public function scopeDueDateBefore(Builder $query, string $date): Builder
    {
        return $query->where('due_date', '<=', $date);
    }

    /**
     * Scope a query to only include items due after or on a given date.
     */
    public function scopeDueDateAfter(Builder $query, string $date): Builder
    {
        return $query->where('due_date', '>=', $date);
    }
}
