<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(Request $request, Course $course): Response
    {
        return Inertia::render('Tenant/Modules/Index', [
            'course' => $course->load(['modules.lessons']),
        ]);
    }

    public function create(Request $request, Course $course): Response
    {
        $this->staff($request);
        $course->load('modules');

        return Inertia::render('Tenant/Modules/Create', ['course' => $course]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $data['course_id'] = $course->id;
        $data['sort_order'] = $data['sort_order'] ?? ($course->modules()->max('sort_order') + 1);
        Module::query()->create($data);

        return redirect()->route('courses.show', $course);
    }

    public function edit(Request $request, Course $course, Module $module): Response
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);

        return Inertia::render('Tenant/Modules/Edit', ['course' => $course, 'module' => $module]);
    }

    public function update(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $module->update($data);

        return redirect()->route('courses.show', $course);
    }

    public function destroy(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);
        $module->delete();

        return redirect()->route('courses.show', $course);
    }

    protected function staff(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
    }
}
