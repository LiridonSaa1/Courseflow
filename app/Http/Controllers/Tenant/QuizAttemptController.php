<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentProgress;
use App\Services\GamificationService;
use App\Services\QuizScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    public function create(Request $request, Quiz $quiz): Response
    {
        $quiz->load('questions');
        if ($quiz->randomize) {
            $quiz->setRelation('questions', $quiz->questions->shuffle()->values());
        }

        return Inertia::render('Tenant/Quizzes/Take', ['quiz' => $quiz]);
    }

    public function store(Request $request, Quiz $quiz, QuizScoringService $scoring, GamificationService $gamify): RedirectResponse
    {
        $quiz->load('questions');
        $answers = $request->validate([
            'answers' => ['required', 'array'],
        ])['answers'];
        $graded = $scoring->score($quiz, $answers);

        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'score' => $graded['score'],
            'max_score' => $graded['max'],
            'answers' => $answers,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $lesson = $quiz->lesson;
        if ($lesson) {
            $pct = $graded['max'] > 0 ? (int) round($graded['score'] / $graded['max'] * 100) : 0;
            StudentProgress::query()->updateOrCreate(
                ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
                ['percent' => max($pct, 50), 'completed_at' => now()]
            );
            $gamify->awardQuizXp($request->user(), $pct);
        }
        $gamify->recordActivity($request->user());

        return redirect()->route('tenant.dashboard')
            ->with('success', 'Quiz submitted. '.$graded['score'].' / '.$graded['max']);
    }
}
