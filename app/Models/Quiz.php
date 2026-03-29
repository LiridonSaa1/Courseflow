<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'lesson',
        'module',
        'final_exam',
        'placement',
    ];

    protected $fillable = [
        'course_id',
        'lesson_id',
        'title',
        'description',
        'type',
        'time_limit_seconds',
        'randomize',
        'pass_mark',
        'total_marks',
        'max_attempts',
        'is_shuffle_questions',
        'is_shuffle_answers',
        'show_results_instantly',
        'allow_retry',
        'negative_marking',
        'show_correct_after_finish',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'randomize' => 'boolean',
            'is_shuffle_questions' => 'boolean',
            'is_shuffle_answers' => 'boolean',
            'show_results_instantly' => 'boolean',
            'allow_retry' => 'boolean',
            'negative_marking' => 'boolean',
            'show_correct_after_finish' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Quiz $quiz) {
            if ($quiz->isDirty('is_shuffle_questions')) {
                $quiz->randomize = $quiz->is_shuffle_questions;
            } elseif ($quiz->isDirty('randomize') && ! $quiz->isDirty('is_shuffle_questions')) {
                $quiz->is_shuffle_questions = (bool) $quiz->randomize;
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Sum of question points; prefers stored total_marks when set.
     */
    public function computedTotalMarks(): int
    {
        if ($this->total_marks !== null) {
            return (int) $this->total_marks;
        }

        return (int) $this->questions()->sum('points');
    }
}
