<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $courses = Course::query()->with('modules')->latest()->get();

        return Inertia::render('Tenant/Courses/Index', ['courses' => $courses]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeStaff($request);

        return Inertia::render('Tenant/Courses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:32'],
            'level' => ['required', 'string', 'max:8'],
            'description' => ['nullable', 'string'],
        ]);
        $data['created_by'] = $request->user()->id;
        Course::query()->create($data);

        return redirect()->route('courses.index');
    }

    public function show(Request $request, Course $course): Response
    {
        $course->load(['modules.lessons.quizzes']);

        return Inertia::render('Tenant/Courses/Show', ['course' => $course]);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorizeStaff($request);

        return Inertia::render('Tenant/Courses/Edit', ['course' => $course]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeStaff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:32'],
            'level' => ['required', 'string', 'max:8'],
            'description' => ['nullable', 'string'],
        ]);
        $course->update($data);

        return redirect()->route('courses.show', $course);
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeStaff($request);
        $course->delete();

        return redirect()->route('courses.index');
    }

    protected function authorizeStaff(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
    }
}
