<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InnovationProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'cover_url' => $this->cover_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'items_count' => $this->whenCounted('items'),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'content' => $item->content,
                'image' => $item->image,
                'image_url' => $item->image_url,
                'sort_order' => $item->sort_order,
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
