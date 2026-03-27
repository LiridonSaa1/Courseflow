<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AnalyticsController;
use App\Http\Controllers\Tenant\AssignmentController;
use App\Http\Controllers\Tenant\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\CertificateController;
use App\Http\Controllers\Tenant\CommunityController;
use App\Http\Controllers\Tenant\CourseController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\InviteController;
use App\Http\Controllers\Tenant\LessonController;
use App\Http\Controllers\Tenant\LiveSessionController;
use App\Http\Controllers\Tenant\ModuleController;
use App\Http\Controllers\Tenant\QuizAttemptController;
use App\Http\Controllers\Tenant\QuizController;
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

    Route::middleware(['auth', 'tenant.active'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        Route::resource('courses', CourseController::class);
        Route::resource('courses.modules', ModuleController::class)->except(['show']);
        Route::resource('modules.lessons', LessonController::class)->except(['show']);
        Route::resource('lessons.quizzes', QuizController::class)->except(['show']);

        Route::get('quizzes/{quiz}/take', [QuizAttemptController::class, 'create'])->name('quizzes.take');
        Route::post('quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])->name('quizzes.attempts.store');

        Route::resource('students', StudentController::class)->only(['index', 'store', 'destroy']);
        Route::post('invites', [InviteController::class, 'store'])->name('tenant.invites.store');

        Route::resource('teachers', TeacherController::class)->only(['index', 'store', 'destroy']);

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
