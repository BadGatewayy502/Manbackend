<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'button_text' => $this->button_text,
            'is_active' => $this->is_active,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'display_order' => $this->display_order,
            'display_frequency' => $this->display_frequency?->value ?? $this->display_frequency,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
