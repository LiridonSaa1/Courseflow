<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'xp_reward'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievement')->withTimestamps();
    }
}
