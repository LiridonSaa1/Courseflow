<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);

        $avgScores = QuizAttempt::query()
            ->selectRaw('quiz_id, AVG(CASE WHEN max_score > 0 THEN score * 100.0 / max_score END) as pct')
            ->groupBy('quiz_id')
            ->get();

        $weakLessons = StudentProgress::query()
            ->select('lesson_id', DB::raw('AVG(percent) as avg_pct'))
            ->groupBy('lesson_id')
            ->orderBy('avg_pct')
            ->take(10)
            ->get();

        $completion = Course::query()->withCount('modules')->get()->map(function (Course $c) {
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
