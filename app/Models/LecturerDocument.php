<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturerDocument extends Model
{
    protected $fillable = ['lecturer_profile_id', 'original_name', 'file_path'];

    public function lecturerProfile(): BelongsTo
    {
        return $this->belongsTo(LecturerProfile::class);
    }
}
