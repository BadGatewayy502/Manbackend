<?php

namespace App\Models;

use App\Enums\PopupDisplayFrequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PopupBanner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'description',
        'link_url',
        'button_text',
        'is_active',
        'start_at',
        'end_at',
        'display_order',
        'display_frequency',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'display_order' => 'integer',
            'display_frequency' => PopupDisplayFrequency::class,
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? '/storage/'.$this->image : null;
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });
    }
}
