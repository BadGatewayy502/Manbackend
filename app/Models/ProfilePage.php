<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilePage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'content',
        'mission',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProfilePagePhoto::class)->orderBy('sort_order')->orderBy('id');
    }
}
