<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\StudentProgress;
use App\Models\UserStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $courses = Course::query()->withCount('modules')->latest()->take(10)->get();
        $progress = StudentProgress::query()->where('user_id', $user->id)->get();
        $attempts = QuizAttempt::query()->where('user_id', $user->id)->latest()->take(5)->get();
        $stat = UserStat::query()->where('user_id', $user->id)->first();

        $chartActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $chartActivity[] = QuizAttempt::query()
                ->where('user_id', $user->id)
                ->whereDate('created_at', $day)
                ->count();
        }

        return Inertia::render('Tenant/Dashboard', [
            'roleNames' => $user->getRoleNames()->values(),
            'courses' => $courses,
            'progress' => $progress,
            'recentAttempts' => $attempts,
            'stat' => $stat,
            'chartActivity' => $chartActivity,
        ]);
    }
}
