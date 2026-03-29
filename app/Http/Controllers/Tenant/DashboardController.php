<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LiveSession;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Teacher;
use App\Models\UserStat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $coursesQuery = $this->scopeCoursesForStaff($request, Course::query())
            ->withCount(['modules', 'studyClasses'])
            ->latest()
            ->take(10);
        $courses = $coursesQuery->get();
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

        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $uid = $user->id;
            $workspaceStats = [
                'courses' => Course::query()->where('created_by', $uid)->count(),
                'students' => Student::query()
                    ->withoutTrashed()
                    ->whereHas('studyClasses', fn ($q) => $q->where('teacher_id', $tid))
                    ->count(),
                'teachers' => 1,
                'lessons' => Lesson::query()->where('created_by', $uid)->count(),
                'quizzes' => Quiz::query()
                    ->where(function (Builder $q) use ($uid, $tid) {
                        $q->whereHas('lesson', fn (Builder $lq) => $lq->where('created_by', $uid))
                            ->orWhere(function (Builder $q2) use ($uid, $tid) {
                                $q2->whereNull('lesson_id')
                                    ->whereHas('course', fn (Builder $cq) => $cq->where(function (Builder $c2) use ($uid, $tid) {
                                        $c2->where('created_by', $uid)->orWhere('teacher_id', $tid);
                                    }));
                            });
                    })
                    ->count(),
                'liveSessions' => LiveSession::query()
                    ->whereHas('studyClass', fn ($q) => $q->where('teacher_id', $tid))
                    ->count(),
                'attempts' => QuizAttempt::query()
                    ->whereHas('quiz', function (Builder $q) use ($uid, $tid) {
                        $q->where(function (Builder $q2) use ($uid, $tid) {
                            $q2->whereHas('lesson', fn (Builder $lq) => $lq->where('created_by', $uid))
                                ->orWhere(function (Builder $q3) use ($uid, $tid) {
                                    $q3->whereNull('lesson_id')
                                        ->whereHas('course', fn (Builder $cq) => $cq->where(function (Builder $c2) use ($uid, $tid) {
                                            $c2->where('created_by', $uid)->orWhere('teacher_id', $tid);
                                        }));
                                });
                        });
                    })
                    ->count(),
            ];
        } else {
            $workspaceStats = [
                'courses' => Course::query()->count(),
                'students' => Student::query()->withoutTrashed()->count(),
                'teachers' => Teacher::query()->withoutTrashed()->count(),
                'lessons' => Lesson::query()->count(),
                'quizzes' => Quiz::query()->count(),
                'liveSessions' => LiveSession::query()->count(),
                'attempts' => QuizAttempt::query()->count(),
            ];
        }

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
