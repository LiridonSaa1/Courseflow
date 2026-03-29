<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuizHubController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $quizzesQuery = $this->scopeQuizzesForStaff($request, Quiz::query())
            ->with(['lesson.module.course', 'course'])
            ->withCount(['questions', 'attempts'])
            ->latest('id');

        $quizzes = $quizzesQuery->get();

        $ids = $quizzes->pluck('id')->all();
        $avgs = $ids === [] ? collect() : DB::table('quiz_attempts')
            ->whereIn('quiz_id', $ids)
            ->where('status', 'submitted')
            ->groupBy('quiz_id')
            ->selectRaw('quiz_id, AVG(CASE WHEN max_score > 0 THEN score * 100.0 / max_score ELSE NULL END) as avg_pct')
            ->pluck('avg_pct', 'quiz_id');

        $quizzes = $quizzes->map(function (Quiz $quiz) use ($avgs) {
            $avg = $avgs[$quiz->id] ?? null;

            return array_merge($quiz->toArray(), [
                'avg_score_percent' => $avg !== null ? round((float) $avg, 1) : null,
            ]);
        });

        $courses = $this->scopeCoursesForStaff($request, Course::query())
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('title')
            ->get();

        $courseIds = $courses->pluck('id')->all();
        $modules = Module::query()
            ->whereIn('course_id', $courseIds)
            ->with('course')
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->get();

        $archivedQuizzesCount = $this->scopeQuizzesForStaff($request, Quiz::onlyTrashed())->count();

        $lessons = $this->scopeLessonsForStaff($request, Lesson::query())
            ->with(['module.course'])
            ->orderBy('module_id')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Tenant/Quizzes/Index', [
            'quizzes' => $quizzes,
            'courses' => $courses,
            'modules' => $modules,
            'lessons' => $lessons,
            'archivedQuizzesCount' => $archivedQuizzesCount,
        ]);
    }

    public function archive(Request $request): Response
    {
        $this->staff($request);

        $quizzes = $this->scopeQuizzesForStaff($request, Quiz::onlyTrashed())
            ->with(['lesson.module.course', 'course'])
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Tenant/Quizzes/Archive', [
            'quizzes' => $quizzes,
        ]);
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:quizzes,id'],
        ])['ids'];

        $this->scopeQuizzesForStaff($request, Quiz::query())->whereIn('id', $ids)->delete();

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Kuizet u zhvendosën në arkiv.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('quizzes', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeQuizzesForStaff($request, Quiz::onlyTrashed())->whereIn('id', $ids)->restore();

        return redirect()
            ->route('quizzes.archive')
            ->with('success', 'Kuizet u rikthyen te lista kryesore.');
    }

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('quizzes', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeQuizzesForStaff($request, Quiz::onlyTrashed())->whereIn('id', $ids)->forceDelete();

        return redirect()
            ->route('quizzes.archive')
            ->with('success', 'Kuizet u fshinë përgjithmonë.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(Quiz::TYPES)],
            'time_limit_seconds' => ['nullable', 'integer', 'min:0'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'is_shuffle_questions' => ['sometimes', 'boolean'],
            'is_shuffle_answers' => ['sometimes', 'boolean'],
            'show_results_instantly' => ['sometimes', 'boolean'],
            'allow_retry' => ['sometimes', 'boolean'],
            'negative_marking' => ['sometimes', 'boolean'],
            'show_correct_after_finish' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', 'in:draft,published'],
        ]);

        if (empty($data['lesson_id']) && empty($data['course_id'])) {
            return redirect()->back()->withErrors(['course_id' => 'Zgjidh një kurs ose një mësim.']);
        }

        if (! empty($data['lesson_id'])) {
            $lesson = Lesson::query()->with('module.course')->findOrFail($data['lesson_id']);
            $this->authorizeStaffLesson($request, $lesson);
            $data['course_id'] = $lesson->module->course_id;
            $data['lesson_id'] = $lesson->id;
        } else {
            $course = Course::query()->findOrFail($data['course_id']);
            $this->authorizeStaffCourse($request, $course);
            $data['lesson_id'] = null;
        }

        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data['is_shuffle_questions'] = $data['is_shuffle_questions'] ?? false;
        $data['is_shuffle_answers'] = $data['is_shuffle_answers'] ?? false;
        $data['show_results_instantly'] = $data['show_results_instantly'] ?? true;
        $data['allow_retry'] = $data['allow_retry'] ?? true;
        $data['negative_marking'] = $data['negative_marking'] ?? false;
        $data['show_correct_after_finish'] = $data['show_correct_after_finish'] ?? true;
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Quiz::query()->create($data);

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Kuizi u krijua.');
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffQuiz($request, $quiz);
        $data = $request->validate([
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', Rule::in(Quiz::TYPES)],
            'time_limit_seconds' => ['nullable', 'integer', 'min:0'],
            'pass_mark' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'total_marks' => ['nullable', 'integer', 'min:0'],
            'is_shuffle_questions' => ['sometimes', 'boolean'],
            'is_shuffle_answers' => ['sometimes', 'boolean'],
            'show_results_instantly' => ['sometimes', 'boolean'],
            'allow_retry' => ['sometimes', 'boolean'],
            'negative_marking' => ['sometimes', 'boolean'],
            'show_correct_after_finish' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', 'in:draft,published'],
        ]);

        if (array_key_exists('lesson_id', $data)) {
            if ($data['lesson_id'] !== null) {
                $lesson = Lesson::query()->with('module.course')->findOrFail($data['lesson_id']);
                $this->authorizeStaffLesson($request, $lesson);
                $data['course_id'] = $lesson->module->course_id;
            } else {
                $cid = $data['course_id'] ?? $quiz->course_id;
                if ($cid !== null) {
                    $course = Course::query()->findOrFail($cid);
                    $this->authorizeStaffCourse($request, $course);
                    $data['course_id'] = $course->id;
                }
            }
        }

        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'published' && $quiz->published_at === null) {
                $data['published_at'] = now();
            }
            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }
        }

        $data['updated_by'] = $request->user()->id;

        $quiz->update($data);

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Kuizi u përditësua.');
    }

    public function duplicate(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffQuiz($request, $quiz);
        $quiz->load('questions');

        $copy = $quiz->replicate();
        $copy->title = $quiz->title.' (copy)';
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->deleted_at = null;
        $copy->created_by = $request->user()->id;
        $copy->updated_by = $request->user()->id;
        $copy->save();

        foreach ($quiz->questions as $question) {
            $q = $question->replicate();
            $q->quiz_id = $copy->id;
            $q->save();
        }

        $copy->update(['total_marks' => $copy->questions()->sum('points')]);

        return redirect()
            ->route('quizzes.index')
            ->with('success', 'Kuizi u duplikua.');
    }
}
