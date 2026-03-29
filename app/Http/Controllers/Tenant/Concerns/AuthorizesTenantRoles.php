<?php

namespace App\Http\Controllers\Tenant\Concerns;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
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
        $uid = (int) $request->user()->id;
        $isCreator = (int) ($course->created_by ?? 0) === $uid;
        $isAssignedTeacher = (int) ($course->teacher_id ?? 0) === $tid;
        abort_unless($isCreator || $isAssignedTeacher, 403);
    }

    protected function authorizeStaffModule(Request $request, Module $module): void
    {
        $module->loadMissing('course');
        $this->authorizeStaffCourse($request, $module->course);
    }

    /**
     * Teachers may only manage lessons they created (created_by).
     */
    protected function authorizeStaffLesson(Request $request, Lesson $lesson): void
    {
        $tid = $this->staffTeacherId($request);
        if ($tid === null) {
            return;
        }
        abort_unless((int) $lesson->created_by === (int) $request->user()->id, 403);
    }

    /**
     * Teachers may manage quizzes tied to their lessons, or course-level quizzes for courses they own/teach.
     */
    protected function authorizeStaffQuiz(Request $request, Quiz $quiz): void
    {
        $tid = $this->staffTeacherId($request);
        if ($tid === null) {
            return;
        }
        $quiz->loadMissing(['lesson', 'course']);
        if ($quiz->lesson_id !== null && $quiz->lesson !== null) {
            $this->authorizeStaffLesson($request, $quiz->lesson);

            return;
        }
        if ($quiz->course_id !== null && $quiz->course !== null) {
            $this->authorizeStaffCourse($request, $quiz->course);

            return;
        }
        abort(403);
    }

    /**
     * @param  Builder<Quiz>  $query
     * @return Builder<Quiz>
     */
    protected function scopeQuizzesForStaff(Request $request, Builder $query): Builder
    {
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $uid = (int) $request->user()->id;
            $query->where(function (Builder $q) use ($uid, $tid) {
                $q->whereHas('lesson', fn (Builder $lq) => $lq->where('created_by', $uid))
                    ->orWhere(function (Builder $q2) use ($uid, $tid) {
                        $q2->whereNull('lesson_id')
                            ->whereNotNull('course_id')
                            ->whereHas('course', function (Builder $cq) use ($uid, $tid) {
                                $cq->where(function (Builder $c2) use ($uid, $tid) {
                                    $c2->where('created_by', $uid)
                                        ->orWhere('teacher_id', $tid);
                                });
                            });
                    });
            });
        }

        return $query;
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    protected function scopeCoursesForStaff(Request $request, Builder $query): Builder
    {
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $uid = $request->user()->id;
            $query->where(function (Builder $q) use ($uid, $tid) {
                $q->where('created_by', $uid)
                    ->orWhere('teacher_id', $tid);
            });
        }

        return $query;
    }

    /**
     * @param  Builder<Lesson>  $query
     * @return Builder<Lesson>
     */
    protected function scopeLessonsForStaff(Request $request, Builder $query): Builder
    {
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $query->where('created_by', $request->user()->id);
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
