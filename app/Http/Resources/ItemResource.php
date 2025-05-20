<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource; // For creator and assignee
use App\Http\Resources\TagResource;  // For tags

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date ? $this->due_date->toDateString() : null,
            'position' => $this->position,
            'status' => $this->status,
            'priority' => $this->priority,
            'section_id' => $this->section_id,

            // Relationships
            'user' => new UserResource($this->whenLoaded('user')), // Creator
            'assignee' => new UserResource($this->whenLoaded('assignee')), // Assigned user
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Deprecated direct IDs, prefer nested resources for related entities
            // 'user_id' => $this->user_id, 
            // 'assigned_to_user_id' => $this->assigned_to, 
        ];
    }
}
