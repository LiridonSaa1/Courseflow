<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\StudentProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request, Module $module): Response
    {
        $this->authorizeStaffModule($request, $module);

        return Inertia::render('Tenant/Lessons/ModuleLessons', [
            'module' => $module->load(['course', 'lessons.quizzes']),
        ]);
    }

    public function create(Request $request, Module $module): Response
    {
        $this->staff($request);
        $this->authorizeStaffModule($request, $module);
        $module->load('course');

        return Inertia::render('Tenant/Lessons/Create', ['module' => $module]);
    }

    public function store(Request $request, Module $module): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffModule($request, $module);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(Lesson::TYPES)],
            'level' => ['nullable', 'string', 'max:8'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);
        $data['module_id'] = $module->id;
        $data['sort_order'] = $data['sort_order'] ?? (($module->lessons()->max('sort_order') ?? 0) + 1);
        $data['slug'] = Lesson::uniqueSlugForModule($module->id, $data['title']);
        $data['created_by'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'draft';
        if (($data['status'] ?? '') === 'published') {
            $data['published_at'] = now();
        }
        Lesson::query()->create($data);

        return redirect()->route('modules.lessons.index', $module);
    }

    public function show(Request $request, Module $module, Lesson $lesson): Response
    {
        abort_unless($lesson->module_id === $module->id, 404);

        if ($request->user()?->hasAnyRole(['owner', 'admin', 'teacher'])) {
            $this->authorizeStaffModule($request, $module);
        }

        $lesson->load(['module.course', 'quizzes', 'contents']);

        $prev = Lesson::query()
            ->where('module_id', $module->id)
            ->where('sort_order', '<', $lesson->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        $next = Lesson::query()
            ->where('module_id', $module->id)
            ->where('sort_order', '>', $lesson->sort_order)
            ->orderBy('sort_order')
            ->first();

        $progress = StudentProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $canManage = $request->user()?->hasAnyRole(['owner', 'admin', 'teacher']) ?? false;

        return Inertia::render('Tenant/Lessons/Show', [
            'lesson' => $lesson,
            'prevLesson' => $prev,
            'nextLesson' => $next,
            'progress' => $progress,
            'canManage' => $canManage,
        ]);
    }

    public function edit(Request $request, Module $module, Lesson $lesson): Response
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $this->authorizeStaffModule($request, $module);
        $lesson->load('module.course');

        return Inertia::render('Tenant/Lessons/Edit', ['module' => $module, 'lesson' => $lesson]);
    }

    public function update(Request $request, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $this->authorizeStaffModule($request, $module);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(Lesson::TYPES)],
            'level' => ['nullable', 'string', 'max:8'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);
        $data['updated_by'] = $request->user()->id;
        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'published' && $lesson->published_at === null) {
                $data['published_at'] = now();
            }
            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }
        }
        $lesson->update($data);

        return redirect()->route('modules.lessons.show', [$module, $lesson]);
    }

    public function destroy(Request $request, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $this->authorizeStaffModule($request, $module);
        $lesson->delete();

        return redirect()->route('modules.lessons.index', $module);
    }
}
