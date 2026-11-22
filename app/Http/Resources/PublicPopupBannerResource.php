<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPopupBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'button_text' => $this->button_text,
            'display_order' => $this->display_order,
            'display_frequency' => $this->display_frequency?->value ?? $this->display_frequency,
        ];
    }
}
