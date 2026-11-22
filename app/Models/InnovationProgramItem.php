<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InnovationProgramItem extends Model
{
    protected $fillable = [
        'innovation_program_id',
        'title',
        'content',
        'image',
        'sort_order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(InnovationProgram::class, 'innovation_program_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
