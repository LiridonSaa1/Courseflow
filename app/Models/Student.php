<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    use SoftDeletes;

    protected $appends = [
        'profile_photo_url',
    ];

    protected $fillable = [
        'user_id',
        'invite_token',
        'invited_at',
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'gender',
        'profile_photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'date_of_birth' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyClasses(): BelongsToMany
    {
        return $this->belongsToMany(StudyClass::class, 'class_student', 'student_id', 'class_id')->withTimestamps();
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
