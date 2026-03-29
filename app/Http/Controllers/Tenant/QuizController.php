<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request, Lesson $lesson): Response
    {
        $this->authorizeStaffLesson($request, $lesson);

        return Inertia::render('Tenant/Quizzes/LessonQuizzes', [
            'lesson' => $lesson->load(['module.course', 'quizzes.questions']),
        ]);
    }

    public function create(Request $request, Lesson $lesson): Response
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        $lesson->load(['module.course']);

        return Inertia::render('Tenant/Quizzes/Create', ['lesson' => $lesson]);
    }

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        $lesson->load('module.course');

        $quizData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', Rule::in(Quiz::TYPES)],
            'time_limit_seconds' => ['nullable', 'integer', 'min:0'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'randomize' => ['sometimes', 'boolean'],
            'is_shuffle_questions' => ['sometimes', 'boolean'],
            'is_shuffle_answers' => ['sometimes', 'boolean'],
            'show_results_instantly' => ['sometimes', 'boolean'],
            'allow_retry' => ['sometimes', 'boolean'],
            'negative_marking' => ['sometimes', 'boolean'],
            'show_correct_after_finish' => ['sometimes', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', 'string'],
            'questions.*.points' => ['nullable', 'integer'],
            'questions.*.payload' => ['required', 'array'],
            'questions.*.explanation' => ['nullable', 'string'],
        ]);
        $questions = $quizData['questions'];
        unset($quizData['questions']);

        $quizData['course_id'] = $lesson->module->course_id;
        $quizData['lesson_id'] = $lesson->id;
        $quizData['created_by'] = $request->user()->id;
        $quizData['updated_by'] = $request->user()->id;
        $quizData['type'] = $quizData['type'] ?? 'lesson';
        $quizData['status'] = 'published';
        $quizData['published_at'] = now();
        $shuffle = $request->has('is_shuffle_questions')
            ? $request->boolean('is_shuffle_questions')
            : $request->boolean('randomize', false);
        $quizData['is_shuffle_questions'] = $shuffle;
        $quizData['is_shuffle_answers'] = $quizData['is_shuffle_answers'] ?? false;
        $quizData['show_results_instantly'] = $quizData['show_results_instantly'] ?? true;
        $quizData['allow_retry'] = $quizData['allow_retry'] ?? true;
        $quizData['negative_marking'] = $quizData['negative_marking'] ?? false;
        $quizData['show_correct_after_finish'] = $quizData['show_correct_after_finish'] ?? true;
        unset($quizData['randomize']);

        $quiz = Quiz::query()->create($quizData);
        $this->replaceQuizQuestions($quiz, $questions);

        return redirect()->route('lessons.quizzes.index', $lesson);
    }

    public function edit(Request $request, Lesson $lesson, Quiz $quiz): Response
    {
        $this->staff($request);
        abort_unless($quiz->lesson_id === $lesson->id, 404);
        $this->authorizeStaffLesson($request, $lesson);
        $quiz->load('questions');
        $lesson->load('module.course');

        return Inertia::render('Tenant/Quizzes/Edit', ['lesson' => $lesson, 'quiz' => $quiz]);
    }

    public function update(Request $request, Lesson $lesson, Quiz $quiz): RedirectResponse
    {
        $this->staff($request);
        abort_unless($quiz->lesson_id === $lesson->id, 404);
        $this->authorizeStaffLesson($request, $lesson);
        $lesson->load('module.course');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', Rule::in(Quiz::TYPES)],
            'time_limit_seconds' => ['nullable', 'integer', 'min:0'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'randomize' => ['sometimes', 'boolean'],
            'is_shuffle_questions' => ['sometimes', 'boolean'],
            'is_shuffle_answers' => ['sometimes', 'boolean'],
            'show_results_instantly' => ['sometimes', 'boolean'],
            'allow_retry' => ['sometimes', 'boolean'],
            'negative_marking' => ['sometimes', 'boolean'],
            'show_correct_after_finish' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:draft,published'],
            'questions' => ['sometimes', 'array', 'min:1'],
            'questions.*.type' => ['required', 'string'],
            'questions.*.points' => ['nullable', 'integer'],
            'questions.*.payload' => ['required', 'array'],
            'questions.*.explanation' => ['nullable', 'string'],
        ]);

        $questionsPayload = $data['questions'] ?? null;
        unset($data['questions']);

        if ($request->has('is_shuffle_questions')) {
            $data['is_shuffle_questions'] = $request->boolean('is_shuffle_questions');
        } elseif ($request->has('randomize')) {
            $data['is_shuffle_questions'] = $request->boolean('randomize');
        }
        unset($data['randomize']);

        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'published' && $quiz->published_at === null) {
                $data['published_at'] = now();
            }
            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }
        }

        $data['course_id'] = $lesson->module->course_id;
        $data['updated_by'] = $request->user()->id;

        $quiz->update($data);

        if (is_array($questionsPayload)) {
            $this->replaceQuizQuestions($quiz, $questionsPayload);
        }

        return redirect()->route('lessons.quizzes.index', $lesson);
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function replaceQuizQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->delete();
        foreach ($questions as $i => $q) {
            Question::query()->create([
                'quiz_id' => $quiz->id,
                'type' => $q['type'],
                'points' => $q['points'] ?? 1,
                'payload' => $q['payload'],
                'explanation' => $q['explanation'] ?? null,
                'sort_order' => $i,
            ]);
        }

        $quiz->update(['total_marks' => (int) $quiz->questions()->sum('points')]);
    }

    public function destroy(Request $request, Lesson $lesson, Quiz $quiz): RedirectResponse
    {
        $this->staff($request);
        abort_unless($quiz->lesson_id === $lesson->id, 404);
        $this->authorizeStaffLesson($request, $lesson);
        $quiz->delete();

        return redirect()->route('lessons.quizzes.index', $lesson);
    }
}
