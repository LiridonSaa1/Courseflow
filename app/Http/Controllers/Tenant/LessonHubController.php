<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Module;
use App\Models\StudentProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LessonHubController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $lessons = $this->scopeLessonsForStaff($request, Lesson::query())
            ->with(['module.course'])
            ->orderBy('module_id')
            ->orderBy('sort_order')
            ->get();

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

        $archivedLessonsCount = $this->scopeLessonsForStaff($request, Lesson::onlyTrashed())->count();

        return Inertia::render('Tenant/Lessons/Index', [
            'lessons' => $lessons,
            'courses' => $courses,
            'modules' => $modules,
            'archivedLessonsCount' => $archivedLessonsCount,
        ]);
    }

    public function archive(Request $request): Response
    {
        $this->staff($request);

        $lessons = $this->scopeLessonsForStaff($request, Lesson::onlyTrashed())
            ->with(['module.course'])
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Tenant/Lessons/Archive', [
            'lessons' => $lessons,
        ]);
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:lessons,id'],
        ])['ids'];

        $this->scopeLessonsForStaff($request, Lesson::query())->whereIn('id', $ids)->delete();

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Mësimet u zhvendosën në arkiv.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('lessons', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeLessonsForStaff($request, Lesson::onlyTrashed())->whereIn('id', $ids)->restore();

        return redirect()
            ->route('lessons.archive')
            ->with('success', 'Mësimet u rikthyen te lista kryesore.');
    }

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('lessons', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeLessonsForStaff($request, Lesson::onlyTrashed())->whereIn('id', $ids)->forceDelete();

        return redirect()
            ->route('lessons.archive')
            ->with('success', 'Mësimet u fshinë përgjithmonë.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'module_id' => ['required', 'integer', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(Lesson::TYPES)],
            'level' => ['nullable', 'string', 'max:8'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', 'string', 'in:draft,published'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);

        $module = Module::query()->findOrFail($data['module_id']);
        $module->load('course');
        $this->authorizeStaffCourse($request, $module->course);
        $data['sort_order'] = $data['sort_order'] ?? (($module->lessons()->max('sort_order') ?? 0) + 1);
        $data['slug'] = Lesson::uniqueSlugForModule($module->id, $data['title']);
        $data['created_by'] = $request->user()->id;
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Lesson::query()->create($data);

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Mësimi u krijua.');
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        $data = $request->validate([
            'module_id' => ['sometimes', 'integer', 'exists:modules,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', Rule::in(Lesson::TYPES)],
            'level' => ['nullable', 'string', 'max:8'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['sometimes', 'string', 'in:draft,published'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);

        if (isset($data['module_id']) && (int) $data['module_id'] !== (int) $lesson->module_id) {
            $data['module_id'] = (int) $data['module_id'];
            $titleForSlug = (string) ($data['title'] ?? $lesson->title);
            $data['slug'] = Lesson::uniqueSlugForModule($data['module_id'], $titleForSlug, $lesson->id);
        }

        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'published' && $lesson->published_at === null) {
                $data['published_at'] = now();
            }
            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }
        }

        $data['updated_by'] = $request->user()->id;

        $lesson->update($data);

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Mësimi u përditësua.');
    }

    public function duplicate(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        $lesson->load('contents');

        $copy = $lesson->replicate();
        $copy->title = $lesson->title.' (copy)';
        $copy->slug = Lesson::uniqueSlugForModule($lesson->module_id, $copy->title);
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->deleted_at = null;
        $copy->created_by = $request->user()->id;
        $copy->updated_by = $request->user()->id;
        $copy->save();

        foreach ($lesson->contents as $block) {
            $b = $block->replicate();
            $b->lesson_id = $copy->id;
            $b->save();
        }

        return redirect()
            ->route('lessons.index')
            ->with('success', 'Mësimi u duplikua.');
    }

    public function saveProgress(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'completed' => ['sometimes', 'boolean'],
            'last_position' => ['nullable', 'string', 'max:512'],
        ]);

        $userId = (int) $request->user()->id;
        $progress = StudentProgress::query()->firstOrNew([
            'user_id' => $userId,
            'lesson_id' => $lesson->id,
        ]);

        if ($progress->started_at === null) {
            $progress->started_at = now();
        }

        if (array_key_exists('percent', $data) && $data['percent'] !== null) {
            $progress->percent = $data['percent'];
        }

        if (! empty($data['last_position'])) {
            $progress->last_position = $data['last_position'];
        }

        if (! empty($data['completed'])) {
            $progress->percent = 100;
            $progress->completed_at = now();
            $progress->state = 'completed';
        } elseif (($progress->percent ?? 0) > 0 && $progress->completed_at === null) {
            $progress->state = 'in_progress';
        }

        $progress->save();

        return redirect()->back()->with('success', 'Progresi u ruajt.');
    }

    public function storeContent(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        $data = $request->validate([
            'content_type' => ['required', 'string', Rule::in([
                'text', 'video', 'audio', 'image', 'pdf', 'note', 'example', 'exercise',
            ])],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'file_url' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? (($lesson->contents()->max('sort_order') ?? 0) + 1);
        $lesson->contents()->create($data);

        return redirect()->back()->with('success', 'Bloku u shtua.');
    }

    public function destroyContent(Request $request, Lesson $lesson, LessonContent $content): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffLesson($request, $lesson);
        abort_unless((int) $content->lesson_id === (int) $lesson->id, 404);
        $content->delete();

        return redirect()->back()->with('success', 'Bloku u hoq.');
    }
}
