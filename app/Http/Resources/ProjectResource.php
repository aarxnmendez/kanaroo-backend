<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'description' => $this->description,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            // Consider adding sections if frequently needed with projects:
            // 'sections' => SectionResource::collection($this->whenLoaded('sections')),
            // 'sections_count' => $this->whenCounted('sections'),
        ];
    }
}
