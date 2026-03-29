<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $this->staff($request);
        $tid = $this->staffTeacherId($request);

        $avgScoresQuery = QuizAttempt::query()
            ->selectRaw('quiz_id, AVG(CASE WHEN max_score > 0 THEN score * 100.0 / max_score END) as pct')
            ->groupBy('quiz_id');
        if ($tid !== null) {
            $avgScoresQuery->whereHas('quiz.lesson.module.course', fn ($q) => $q->where('teacher_id', $tid));
        }
        $avgScores = $avgScoresQuery->get();

        $weakLessonsQuery = StudentProgress::query()
            ->select('lesson_id', DB::raw('AVG(percent) as avg_pct'))
            ->groupBy('lesson_id')
            ->orderBy('avg_pct')
            ->take(10);
        if ($tid !== null) {
            $weakLessonsQuery->whereHas('lesson.module.course', fn ($q) => $q->where('teacher_id', $tid));
        }
        $weakLessons = $weakLessonsQuery->get();

        $courseQuery = Course::query()->withCount('modules');
        if ($tid !== null) {
            $courseQuery->where('teacher_id', $tid);
        }
        $completion = $courseQuery->get()->map(function (Course $c) {
            $lessons = $c->modules()->withCount('lessons')->get()->sum('lessons_count');

            return [
                'title' => $c->title,
                'modules' => $c->modules_count,
                'lessons' => $lessons,
            ];
        });

        return Inertia::render('Tenant/Analytics', [
            'avgScores' => $avgScores,
            'weakLessons' => $weakLessons,
            'courseOutline' => $completion,
        ]);
    }
}
