<?php

namespace App\Http\Controllers\Tenant\Concerns;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AuthorizesTenantRoles
{
    protected function tenantAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['owner', 'admin']), 403);
    }

    /**
     * Course/content staff: owners, delegated admins, and teachers.
     */
    protected function staff(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['owner', 'admin', 'teacher']), 403);
    }

    /**
     * When non-null, the current user is a teacher and data must be limited to this teacher row.
     * Null means owner/admin (full tenant scope for staff actions).
     */
    protected function staffTeacherId(Request $request): ?int
    {
        $user = $request->user();
        if ($user === null || $user->hasAnyRole(['owner', 'admin'])) {
            return null;
        }
        if (! $user->hasRole('teacher')) {
            return null;
        }
        $id = Teacher::query()->where('user_id', $user->id)->value('id');
        abort_if($id === null, 403);

        return (int) $id;
    }

    protected function authorizeStaffCourse(Request $request, Course $course): void
    {
        $tid = $this->staffTeacherId($request);
        if ($tid === null) {
            return;
        }
        abort_unless($course->teacher_id !== null && (int) $course->teacher_id === $tid, 403);
    }

    protected function authorizeStaffModule(Request $request, Module $module): void
    {
        $module->loadMissing('course');
        $this->authorizeStaffCourse($request, $module->course);
    }

    protected function authorizeStaffLesson(Request $request, Lesson $lesson): void
    {
        $lesson->loadMissing('module.course');
        $this->authorizeStaffCourse($request, $lesson->module->course);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    protected function scopeCoursesForStaff(Request $request, Builder $query): Builder
    {
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $query->where('teacher_id', $tid);
        }

        return $query;
    }

    protected function authorizeStaffStudent(Request $request, Student $student): void
    {
        $tid = $this->staffTeacherId($request);
        if ($tid === null) {
            return;
        }
        $ok = $student->studyClasses()->where('teacher_id', $tid)->exists();
        abort_unless($ok, 403);
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    protected function scopeStudentsForStaff(Request $request, Builder $query): Builder
    {
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $query->whereHas('studyClasses', fn (Builder $sq) => $sq->where('teacher_id', $tid));
        }

        return $query;
    }
}
