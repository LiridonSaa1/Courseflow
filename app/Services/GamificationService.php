<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserStat;

class GamificationService
{
    public function recordActivity(User $user): UserStat
    {
        $stat = UserStat::query()->firstOrCreate(['user_id' => $user->id]);

        $today = now()->toDateString();
        if ($stat->last_activity_date?->toDateString() === now()->subDay()->toDateString()) {
            $stat->streak_days = (int) $stat->streak_days + 1;
        } elseif ($stat->last_activity_date?->toDateString() !== $today) {
            $stat->streak_days = 1;
        }

        $stat->last_activity_date = now()->toDateString();

        $stat->xp += 10;
        $stat->level = max(1, intdiv((int) $stat->xp, 500) + 1);
        $stat->save();

        return $stat;
    }

    public function awardQuizXp(User $user, int $scorePercent): void
    {
        $stat = UserStat::query()->firstOrCreate(['user_id' => $user->id]);
        $stat->xp += max(5, (int) round($scorePercent / 2));
        $stat->level = max(1, intdiv((int) $stat->xp, 500) + 1);
        $stat->save();

        if ($scorePercent >= 90) {
            $ach = Achievement::query()->firstOrCreate(
                ['slug' => 'ace-quiz'],
                ['name' => 'Quiz Ace', 'description' => 'Score 90%+ on a quiz.', 'xp_reward' => 50]
            );
            $user->achievements()->syncWithoutDetaching([$ach->id]);
        }
    }
}
