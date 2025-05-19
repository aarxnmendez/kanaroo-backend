<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ItemResource;

class SectionResource extends JsonResource
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
            'name' => $this->name,
            'position' => $this->position,
            'filter_type' => $this->filter_type,
            'filter_value' => $this->filter_value,
            'item_limit' => $this->item_limit,
            'project_id' => $this->project_id,
            'items' => ItemResource::collection($this->whenLoaded('items')), // Now includes filtered/limited items
            'items_count' => $this->whenCounted('items'), // Total count of items linked to this section before section's own filtering/limiting
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
