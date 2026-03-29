<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'reading',
        'grammar',
        'vocabulary',
        'listening',
        'speaking',
        'writing',
        'mixed',
    ];

    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'short_description',
        'description',
        'type',
        'level',
        'sort_order',
        'content',
        'vocabulary',
        'grammar',
        'examples',
        'thumbnail',
        'video_url',
        'audio_url',
        'transcript',
        'duration_minutes',
        'is_free_preview',
        'is_downloadable',
        'requires_completion',
        'status',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'vocabulary' => 'array',
            'grammar' => 'array',
            'examples' => 'array',
            'is_free_preview' => 'boolean',
            'is_downloadable' => 'boolean',
            'requires_completion' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson): void {
            if (empty($lesson->slug) && ! empty($lesson->title)) {
                $lesson->slug = static::uniqueSlugForModule((int) $lesson->module_id, (string) $lesson->title);
            }
        });

        static::updating(function (Lesson $lesson): void {
            if ($lesson->isDirty('title') && ! $lesson->isDirty('slug')) {
                $lesson->slug = static::uniqueSlugForModule((int) $lesson->module_id, (string) $lesson->title, $lesson->id);
            }
        });
    }

    public static function uniqueSlugForModule(int $moduleId, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'lesson';
        $slug = $base;
        $i = 1;
        while (static::query()
            ->where('module_id', $moduleId)
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(LessonContent::class)->orderBy('sort_order');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
