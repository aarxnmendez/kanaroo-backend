<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Don't include sensitive data
            'email_verified_at' => $this->when($this->email_verified_at, function () {
                return $this->email_verified_at->toIso8601String();
            }),
            // Include pivot data when available (for project role)
            'role' => $this->when(isset($this->pivot) && isset($this->pivot->role), function () {
                return $this->pivot->role;
            }),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
