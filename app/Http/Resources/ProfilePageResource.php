<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'content' => $this->content,
            'mission' => $this->mission,
            'is_active' => (bool) ($this->is_active ?? true),
            'photos' => $this->photos->map(fn ($photo) => [
                'id' => $photo->id,
                'caption' => $photo->caption,
                'image' => $photo->image,
                'image_url' => $photo->image_url,
                'sort_order' => $photo->sort_order,
            ])->values(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
