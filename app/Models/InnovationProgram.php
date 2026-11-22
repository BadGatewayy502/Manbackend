<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class InnovationProgram extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'tagline',
        'description',
        'cover_image',
        'sort_order',
        'is_active',
    ];

    protected $appends = ['cover_url'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InnovationProgramItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }
}
