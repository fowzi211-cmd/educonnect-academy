<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsItem extends Model
{
    protected $fillable = [
        'title_en', 'title_ar', 'excerpt_en', 'excerpt_ar',
        'image_path', 'link_url', 'position', 'is_published', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function title(): string
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function excerpt(): string
    {
        return app()->getLocale() === 'ar' ? $this->excerpt_ar : $this->excerpt_en;
    }
}
