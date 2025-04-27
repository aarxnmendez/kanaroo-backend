<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectUser extends Pivot
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'role'
    ];

    /**
     * Get the project associated with this relationship.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user associated with this relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
