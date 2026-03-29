<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $courses = $this->scopeCoursesForStaff($request, Course::query())
            ->with(['modules', 'teacher.user'])
            ->latest()
            ->get();

        $teachersQuery = Teacher::query()->with('user')->orderBy('id');
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $teachersQuery->where('id', $tid);
        }
        $teachers = $teachersQuery->get();

        $archived = $this->scopeCoursesForStaff($request, Course::onlyTrashed());

        return Inertia::render('Tenant/Courses/Index', [
            'courses' => $courses,
            'teachers' => $teachers,
            'archivedCoursesCount' => $archived->count(),
        ]);
    }

    public function archive(Request $request): Response
    {
        $this->staff($request);

        $courses = $this->scopeCoursesForStaff($request, Course::onlyTrashed())
            ->with(['modules', 'teacher.user'])
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Tenant/Courses/Archive', [
            'courses' => $courses,
        ]);
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:courses,id'],
        ])['ids'];

        $q = $this->scopeCoursesForStaff($request, Course::query())->whereIn('id', $ids);
        $q->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Kurset u zhvendosën në arkiv.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('courses', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeCoursesForStaff($request, Course::onlyTrashed())
            ->whereIn('id', $ids)
            ->restore();

        return redirect()
            ->route('courses.archive')
            ->with('success', 'Kurset u rikthyen te lista kryesore.');
    }

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('courses', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $this->scopeCoursesForStaff($request, Course::onlyTrashed())
            ->whereIn('id', $ids)
            ->forceDelete();

        return redirect()
            ->route('courses.archive')
            ->with('success', 'Kurset u fshinë përgjithmonë.');
    }

    public function create(Request $request): Response
    {
        $this->staff($request);

        return Inertia::render('Tenant/Courses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:32'],
            'level' => ['required', 'string', 'max:8'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'string', 'in:draft,published'],
        ]);
        $data['created_by'] = $request->user()->id;
        if (($tid = $this->staffTeacherId($request)) !== null) {
            $data['teacher_id'] = $tid;
        }
        Course::query()->create($data);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function show(Request $request, Course $course): Response
    {
        if ($request->user()?->hasAnyRole(['owner', 'admin', 'teacher'])) {
            $this->authorizeStaffCourse($request, $course);
        }

        $course->load(['modules.lessons.quizzes']);

        return Inertia::render('Tenant/Courses/Show', ['course' => $course]);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->staff($request);
        $this->authorizeStaffCourse($request, $course);

        $teachersQuery = Teacher::query()->with('user')->orderBy('id');
        $teacherScopeId = $this->staffTeacherId($request);
        if ($teacherScopeId !== null) {
            $teachersQuery->where('id', $teacherScopeId);
        }
        $teachers = $teachersQuery->get();

        return Inertia::render('Tenant/Courses/Edit', [
            'course' => $course->load('teacher.user'),
            'teachers' => $teachers,
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffCourse($request, $course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:32'],
            'level' => ['required', 'string', 'max:8'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'string', 'in:draft,published'],
        ]);
        if (($tid = $this->staffTeacherId($request)) !== null) {
            $data['teacher_id'] = $tid;
        }
        $course->update($data);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffCourse($request, $course);
        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
