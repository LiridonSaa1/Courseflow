<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'type',
        'sort_order',
        'content',
        'vocabulary',
        'grammar',
        'examples',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'vocabulary' => 'array',
            'grammar' => 'array',
            'examples' => 'array',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
