<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(Request $request, Module $module): Response
    {
        return Inertia::render('Tenant/Lessons/Index', [
            'module' => $module->load(['course', 'lessons.quizzes']),
        ]);
    }

    public function create(Request $request, Module $module): Response
    {
        $this->staff($request);
        $module->load('course');

        return Inertia::render('Tenant/Lessons/Create', ['module' => $module]);
    }

    public function store(Request $request, Module $module): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);
        $data['module_id'] = $module->id;
        $data['sort_order'] = $data['sort_order'] ?? ($module->lessons()->max('sort_order') + 1);
        Lesson::query()->create($data);

        return redirect()->route('modules.lessons.index', $module);
    }

    public function show(Request $request, Module $module, Lesson $lesson): Response
    {
        abort_unless($lesson->module_id === $module->id, 404);
        $lesson->load(['module.course', 'quizzes']);

        return Inertia::render('Tenant/Lessons/Show', ['lesson' => $lesson]);
    }

    public function edit(Request $request, Module $module, Lesson $lesson): Response
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $lesson->load('module.course');

        return Inertia::render('Tenant/Lessons/Edit', ['module' => $module, 'lesson' => $lesson]);
    }

    public function update(Request $request, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer'],
            'content' => ['nullable', 'array'],
            'vocabulary' => ['nullable', 'array'],
            'grammar' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
        ]);
        $lesson->update($data);

        return redirect()->route('modules.lessons.show', [$module, $lesson]);
    }

    public function destroy(Request $request, Module $module, Lesson $lesson): RedirectResponse
    {
        $this->staff($request);
        abort_unless($lesson->module_id === $module->id, 404);
        $lesson->delete();

        return redirect()->route('modules.lessons.index', $module);
    }

    protected function staff(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
    }
}
