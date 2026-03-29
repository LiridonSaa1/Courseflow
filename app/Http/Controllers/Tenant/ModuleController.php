<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request, Course $course): Response
    {
        $this->authorizeStaffCourse($request, $course);

        return Inertia::render('Tenant/Modules/Index', [
            'course' => $course->load(['modules.lessons']),
        ]);
    }

    public function create(Request $request, Course $course): Response
    {
        $this->staff($request);
        $this->authorizeStaffCourse($request, $course);
        $course->load('modules');

        return Inertia::render('Tenant/Modules/Create', ['course' => $course]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ]);
        $data['course_id'] = $course->id;
        $data['sort_order'] = $data['sort_order'] ?? (($course->modules()->max('sort_order') ?? 0) + 1);
        $data['status'] = $data['status'] ?? 'published';
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        } else {
            $data['published_at'] = null;
        }

        Module::query()->create($data);

        return redirect()->route('courses.show', $course);
    }

    public function edit(Request $request, Course $course, Module $module): Response
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);
        $this->authorizeStaffCourse($request, $course);

        return Inertia::render('Tenant/Modules/Edit', ['course' => $course, 'module' => $module]);
    }

    public function update(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);
        $this->authorizeStaffCourse($request, $course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['sometimes', 'string', 'in:draft,published'],
        ]);

        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'published' && $module->published_at === null) {
                $data['published_at'] = now();
            }
            if ($data['status'] === 'draft') {
                $data['published_at'] = null;
            }
        }

        $data['updated_by'] = $request->user()->id;

        $module->update($data);

        return redirect()->route('courses.show', $course);
    }

    public function destroy(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->staff($request);
        abort_unless($module->course_id === $course->id, 404);
        $this->authorizeStaffCourse($request, $course);
        $module->delete();

        return redirect()->route('courses.show', $course);
    }
}
