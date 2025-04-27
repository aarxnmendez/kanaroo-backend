<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the projects created by the user.
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the projects where the user is a collaborator.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
                    ->using(ProjectUser::class)
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get the items created by the user.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the items assigned to the user.
     */
    public function assignedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'assigned_to');
    }
}
