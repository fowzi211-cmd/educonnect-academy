<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question_en', 'question_ar', 'answer_en', 'answer_ar', 'position', 'is_published'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function question(): string
    {
        return app()->getLocale() === 'ar' ? $this->question_ar : $this->question_en;
    }

    public function answer(): string
    {
        return app()->getLocale() === 'ar' ? $this->answer_ar : $this->answer_en;
    }
}
