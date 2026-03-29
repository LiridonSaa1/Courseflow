<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AnalyticsController;
use App\Http\Controllers\Tenant\AssignmentController;
use App\Http\Controllers\Tenant\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\CertificateController;
use App\Http\Controllers\Tenant\ComingSoonController;
use App\Http\Controllers\Tenant\CommunityController;
use App\Http\Controllers\Tenant\CourseController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InviteController;
use App\Http\Controllers\Tenant\LessonController;
use App\Http\Controllers\Tenant\LessonHubController;
use App\Http\Controllers\Tenant\LiveSessionController;
use App\Http\Controllers\Tenant\ModuleController;
use App\Http\Controllers\Tenant\ProgressController;
use App\Http\Controllers\Tenant\QuizAttemptController;
use App\Http\Controllers\Tenant\QuizController;
use App\Http\Controllers\Tenant\QuizHubController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\SpeakingController;
use App\Http\Controllers\Tenant\StudentController;
use App\Http\Controllers\Tenant\TeacherController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('certificates/verify/{token}', [CertificateController::class, 'verify'])->name('tenant.certificates.verify');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('tenant.login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('tenant.login.store');
    });

    Route::middleware(['auth', 'tenant.session', 'tenant.active', 'tenant.student.active', 'tenant.teacher.active'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        Route::get('lessons/archive', [LessonHubController::class, 'archive'])->name('lessons.archive');
        Route::post('lessons/bulk-archive', [LessonHubController::class, 'bulkArchive'])->name('lessons.bulk-archive');
        Route::post('lessons/bulk-restore', [LessonHubController::class, 'bulkRestore'])->name('lessons.bulk-restore');
        Route::post('lessons/bulk-force-delete', [LessonHubController::class, 'bulkForceDelete'])->name('lessons.bulk-force-delete');
        Route::post('lessons', [LessonHubController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}', [LessonHubController::class, 'update'])->name('lessons.patch');
        Route::post('lessons/{lesson}/duplicate', [LessonHubController::class, 'duplicate'])->name('lessons.duplicate');
        Route::post('lessons/{lesson}/progress', [LessonHubController::class, 'saveProgress'])->name('lessons.progress');
        Route::post('lessons/{lesson}/contents', [LessonHubController::class, 'storeContent'])->name('lessons.contents.store');
        Route::delete('lessons/{lesson}/contents/{content}', [LessonHubController::class, 'destroyContent'])->name('lessons.contents.destroy');
        Route::get('lessons', [LessonHubController::class, 'index'])->name('lessons.index');
        Route::get('quizzes/archive', [QuizHubController::class, 'archive'])->name('quizzes.archive');
        Route::post('quizzes/bulk-archive', [QuizHubController::class, 'bulkArchive'])->name('quizzes.bulk-archive');
        Route::post('quizzes/bulk-restore', [QuizHubController::class, 'bulkRestore'])->name('quizzes.bulk-restore');
        Route::post('quizzes/bulk-force-delete', [QuizHubController::class, 'bulkForceDelete'])->name('quizzes.bulk-force-delete');
        Route::post('quizzes', [QuizHubController::class, 'store'])->name('quizzes.store');
        Route::patch('quizzes/{quiz}', [QuizHubController::class, 'update'])->name('quizzes.patch');
        Route::post('quizzes/{quiz}/duplicate', [QuizHubController::class, 'duplicate'])->name('quizzes.duplicate');
        Route::get('quizzes', [QuizHubController::class, 'index'])->name('quizzes.index');
        Route::get('settings', SettingsController::class)->name('tenant.settings');
        Route::get('progress', ProgressController::class)->name('tenant.progress');

        Route::get('coming-soon/{feature}', [ComingSoonController::class, 'show'])
            ->where('feature', '[a-z0-9-]+')
            ->name('tenant.coming-soon');

        Route::get('courses/archive', [CourseController::class, 'archive'])->name('courses.archive');
        Route::post('courses/bulk-archive', [CourseController::class, 'bulkArchive'])->name('courses.bulk-archive');
        Route::post('courses/bulk-restore', [CourseController::class, 'bulkRestore'])->name('courses.bulk-restore');
        Route::post('courses/bulk-force-delete', [CourseController::class, 'bulkForceDelete'])->name('courses.bulk-force-delete');
        Route::resource('courses', CourseController::class);
        Route::resource('courses.modules', ModuleController::class)->except(['show']);
        Route::resource('modules.lessons', LessonController::class);
        Route::resource('lessons.quizzes', QuizController::class)->except(['show']);

        Route::get('quizzes/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quizzes.attempts.show');
        Route::get('quizzes/{quiz}/attempts/{attempt}/review', [QuizAttemptController::class, 'review'])->name('quizzes.attempts.review');
        Route::get('quizzes/{quiz}/take', [QuizAttemptController::class, 'create'])->name('quizzes.take');
        Route::post('quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])->name('quizzes.attempts.store');

        Route::get('students/archive', [StudentController::class, 'archive'])->name('students.archive');
        Route::post('students/bulk-archive', [StudentController::class, 'bulkArchive'])->name('students.bulk-archive');
        Route::post('students/bulk-restore', [StudentController::class, 'bulkRestore'])->name('students.bulk-restore');
        Route::post('students/bulk-force-delete', [StudentController::class, 'bulkForceDelete'])->name('students.bulk-force-delete');
        Route::resource('students', StudentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('invites', [InviteController::class, 'store'])->name('tenant.invites.store');

        Route::get('teachers/archive', [TeacherController::class, 'archive'])->name('teachers.archive');
        Route::post('teachers/bulk-archive', [TeacherController::class, 'bulkArchive'])->name('teachers.bulk-archive');
        Route::post('teachers/bulk-restore', [TeacherController::class, 'bulkRestore'])->name('teachers.bulk-restore');
        Route::post('teachers/bulk-force-delete', [TeacherController::class, 'bulkForceDelete'])->name('teachers.bulk-force-delete');
        Route::resource('teachers', TeacherController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('tenant.analytics');

        Route::post('lessons/{lesson}/speaking', [SpeakingController::class, 'store'])->name('tenant.speaking.store');

        Route::get('assignments/{assignment}/submit', [AssignmentController::class, 'create'])->name('tenant.assignments.submit');
        Route::post('assignments/{assignment}/submit', [AssignmentController::class, 'store'])->name('tenant.assignments.store');
        Route::post('submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('tenant.submissions.grade');

        Route::get('live-sessions', [LiveSessionController::class, 'index'])->name('tenant.live-sessions.index');
        Route::post('live-sessions', [LiveSessionController::class, 'store'])->name('tenant.live-sessions.store');
        Route::get('live-sessions/{liveSession}/join', [LiveSessionController::class, 'join'])->name('tenant.live-sessions.join');

        Route::get('community', [CommunityController::class, 'index'])->name('tenant.community.index');
        Route::post('community', [CommunityController::class, 'store'])->name('tenant.community.store');
        Route::post('community/{discussion}/reply', [CommunityController::class, 'reply'])->name('tenant.community.reply');

        Route::get('certificates', [CertificateController::class, 'index'])->name('tenant.certificates.index');
        Route::post('certificates/generate', [CertificateController::class, 'generate'])->name('tenant.certificates.generate');
    });
});
