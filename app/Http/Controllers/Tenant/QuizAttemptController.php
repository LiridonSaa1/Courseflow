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
        if ($quiz->status !== 'published') {
            abort(404);
        }

        $quiz->loadCount('questions');

        $submittedCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'submitted')
            ->count();

        $maxAttempts = $quiz->max_attempts;
        $canStart = $maxAttempts === null || $submittedCount < $maxAttempts;

        $started = $request->boolean('started');

        if ($started) {
            abort_unless($canStart, 403);
            $quiz->load('questions');
            if ($quiz->is_shuffle_questions) {
                $quiz->setRelation('questions', $quiz->questions->shuffle()->values());
            }
            if ($quiz->is_shuffle_answers) {
                foreach ($quiz->questions as $q) {
                    if ($q->type === 'multiple_choice' && isset($q->payload['options']) && is_array($q->payload['options'])) {
                        $opts = $q->payload['options'];
                        shuffle($opts);
                        $p = $q->payload;
                        $p['options'] = $opts;
                        $q->setAttribute('payload', $p);
                    }
                }
            }
        } else {
            $quiz->setRelation('questions', collect());
        }

        return Inertia::render('Tenant/Quizzes/Take', [
            'quiz' => $quiz,
            'started' => $started,
            'meta' => [
                'question_count' => (int) $quiz->questions_count,
                'attempts_used' => $submittedCount,
                'attempts_remaining' => $maxAttempts !== null ? max(0, $maxAttempts - $submittedCount) : null,
                'can_start' => $canStart,
            ],
        ]);
    }

    public function store(Request $request, Quiz $quiz, QuizScoringService $scoring, GamificationService $gamify): RedirectResponse
    {
        if ($quiz->status !== 'published') {
            abort(404);
        }

        $submittedCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'submitted')
            ->count();

        if ($quiz->max_attempts !== null && $submittedCount >= $quiz->max_attempts) {
            return redirect()
                ->back()
                ->withErrors(['quiz' => 'You have reached the maximum number of attempts for this quiz.']);
        }

        $quiz->load('questions');
        $answers = $request->validate([
            'answers' => ['required', 'array'],
        ])['answers'];

        $startedAt = now();
        $graded = $scoring->score($quiz, $answers);
        $max = $graded['max'];
        $score = $graded['score'];
        $percent = $max > 0 ? (int) round($score / $max * 100) : 0;
        $passMark = $quiz->pass_mark;
        $passed = $passMark === null ? null : $percent >= (int) $passMark;

        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'score' => $score,
            'max_score' => $max,
            'answers' => $answers,
            'result_summary' => [
                'score' => $score,
                'max' => $max,
                'percent' => $percent,
                'details' => $graded['details'],
            ],
            'status' => 'submitted',
            'submitted_at' => now(),
            'started_at' => $startedAt,
            'completed_at' => now(),
            'passed' => $passed,
        ]);

        $lesson = $quiz->lesson;
        if ($lesson) {
            $pct = $max > 0 ? (int) round($score / $max * 100) : 0;
            StudentProgress::query()->updateOrCreate(
                ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
                ['percent' => max($pct, 50), 'completed_at' => now()]
            );
            $gamify->awardQuizXp($request->user(), $pct);
        }
        $gamify->recordActivity($request->user());

        return redirect()
            ->route('quizzes.attempts.show', [$quiz, $attempt])
            ->with('success', 'Quiz submitted. '.$score.' / '.$max);
    }

    public function show(Request $request, Quiz $quiz, QuizAttempt $attempt): Response
    {
        abort_unless((int) $attempt->quiz_id === (int) $quiz->id, 404);
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 403);

        $summary = $attempt->result_summary ?? [];
        $percent = isset($summary['percent']) ? (int) $summary['percent'] : ($attempt->max_score > 0
            ? (int) round($attempt->score / $attempt->max_score * 100)
            : 0);

        $submittedCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'submitted')
            ->count();

        $maxAttempts = $quiz->max_attempts;
        $canRetry = ($quiz->allow_retry ?? true)
            && ($maxAttempts === null || $submittedCount < $maxAttempts);

        return Inertia::render('Tenant/Quizzes/Result', [
            'quiz' => $quiz->loadMissing(['lesson.module.course', 'course']),
            'attempt' => $attempt,
            'percent' => $percent,
            'can_retry' => $canRetry,
            'show_results_instantly' => $quiz->show_results_instantly ?? true,
        ]);
    }

    public function review(Request $request, Quiz $quiz, QuizAttempt $attempt): Response
    {
        abort_unless((int) $attempt->quiz_id === (int) $quiz->id, 404);
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 403);

        $quiz->load('questions');
        $answers = $attempt->answers ?? [];
        $details = $attempt->result_summary['details'] ?? [];
        $showCorrect = $quiz->show_correct_after_finish ?? true;

        $items = [];
        foreach ($quiz->questions as $question) {
            $p = $question->payload ?? [];
            $given = $answers[$question->id] ?? null;
            $detail = $details[$question->id] ?? $details[(string) $question->id] ?? [];
            $correct = (bool) ($detail['correct'] ?? false);

            $correctAnswer = null;
            if ($showCorrect) {
                $correctAnswer = $this->formatCorrectAnswer($question->type, $p);
            }

            $items[] = [
                'id' => $question->id,
                'type' => $question->type,
                'question_text' => $p['question'] ?? '',
                'points' => $question->points,
                'student_answer' => $given,
                'correct' => $correct,
                'correct_answer' => $correctAnswer,
                'explanation' => $question->explanation,
            ];
        }

        return Inertia::render('Tenant/Quizzes/Review', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'items' => $items,
            'show_correct' => $showCorrect,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function formatCorrectAnswer(string $type, array $payload): ?string
    {
        return match ($type) {
            'multiple_choice' => isset($payload['options'], $payload['correct'])
                ? (string) ($payload['options'][$payload['correct']] ?? $payload['correct'])
                : null,
            'fill_blank', 'translation' => is_array($payload['answers'] ?? null)
                ? implode(' / ', $payload['answers'])
                : (string) ($payload['answers'] ?? ''),
            'reorder' => isset($payload['correct_order']) ? json_encode($payload['correct_order']) : null,
            'match_pairs' => isset($payload['pairs']) ? json_encode($payload['pairs']) : null,
            'listening', 'image_based' => isset($payload['options'], $payload['correct'])
                ? (string) ($payload['options'][$payload['correct']] ?? $payload['correct'])
                : (string) ($payload['correct'] ?? ''),
            default => null,
        };
    }
}
