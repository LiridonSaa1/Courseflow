<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeakingAttempt extends Model
{
    protected $fillable = ['user_id', 'lesson_id', 'audio_path', 'transcript', 'reference_text', 'scores'];

    protected function casts(): array
    {
        return [
            'scores' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
