<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Discussion extends Model
{
    protected $fillable = ['title', 'body', 'user_id', 'discussable_type', 'discussable_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discussable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class);
    }
}
