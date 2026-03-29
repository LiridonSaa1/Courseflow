<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request, Lesson $lesson): Response
    {
        $this->authorizeStaffLesson($request, $lesson);

        return Inertia::render('Tenant/Quizzes/Index', [
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
        $quizData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'time_limit_seconds' => ['nullable', 'integer'],
            'randomize' => ['boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', 'string'],
            'questions.*.points' => ['nullable', 'integer'],
            'questions.*.payload' => ['required', 'array'],
            'questions.*.explanation' => ['nullable', 'string'],
        ]);
        $questions = $quizData['questions'];
        unset($quizData['questions']);
        $quizData['lesson_id'] = $lesson->id;
        $quizData['randomize'] = $quizData['randomize'] ?? false;

        $quiz = Quiz::query()->create($quizData);
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'time_limit_seconds' => ['nullable', 'integer'],
            'randomize' => ['boolean'],
        ]);
        $quiz->update($data);

        return redirect()->route('lessons.quizzes.index', $lesson);
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
