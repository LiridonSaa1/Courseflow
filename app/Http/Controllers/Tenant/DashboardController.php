<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LiveSession;
use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\StudentProgress;
use App\Models\Student;
use App\Models\Teacher;
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
        $courses = Course::query()->withCount(['modules', 'studyClasses'])->latest()->take(10)->get();
        $progress = StudentProgress::query()
            ->with('lesson:id,title')
            ->where('user_id', $user->id)
            ->get();
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

        $workspaceStats = [
            'courses' => Course::query()->count(),
            'students' => Student::query()->count(),
            'teachers' => Teacher::query()->count(),
            'lessons' => Lesson::query()->count(),
            'quizzes' => Quiz::query()->count(),
            'liveSessions' => LiveSession::query()->count(),
            'attempts' => QuizAttempt::query()->count(),
        ];

        return Inertia::render('Tenant/Dashboard', [
            'roleNames' => $user->getRoleNames()->values(),
            'courses' => $courses,
            'progress' => $progress,
            'recentAttempts' => $attempts,
            'stat' => $stat,
            'chartActivity' => $chartActivity,
            'workspaceStats' => $workspaceStats,
        ]);
    }
}
