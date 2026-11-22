<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProfilePagePhoto extends Model
{
    protected $fillable = [
        'profile_page_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(ProfilePage::class, 'profile_page_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
