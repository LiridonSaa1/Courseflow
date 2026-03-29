<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
    use SoftDeletes;

    protected $appends = [
        'profile_photo_url',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'phone',
        'profile_photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyClasses(): HasMany
    {
        return $this->hasMany(StudyClass::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        $p = $this->attributes['profile_photo'] ?? null;
        if ($p === null || $p === '') {
            return null;
        }
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }
        if (str_starts_with($p, '/')) {
            return $p;
        }

        return Storage::disk('public')->url($p);
    }
}
