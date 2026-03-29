<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    /**
     * Learning progress overview (primarily for students; staff may use analytics).
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $progress = StudentProgress::query()
            ->with('lesson:id,title')
            ->where('user_id', $user->id)
            ->get();

        $recentAttempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $chartActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $chartActivity[] = QuizAttempt::query()
                ->where('user_id', $user->id)
                ->whereDate('created_at', $day)
                ->count();
        }

        return Inertia::render('Tenant/Progress/Index', [
            'progress' => $progress,
            'recentAttempts' => $recentAttempts,
            'chartActivity' => $chartActivity,
        ]);
    }
}
