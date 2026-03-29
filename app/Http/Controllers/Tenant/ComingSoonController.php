<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ComingSoonController extends Controller
{
    public function show(string $feature): Response
    {
        $labels = [
            'classes' => 'Classes',
            'assignments' => 'Assignments',
            'attendance' => 'Attendance',
            'reports' => 'Reports',
            'subscription' => 'Subscription',
            'payments' => 'Payments',
            'invoices' => 'Invoices',
            'branding' => 'Branding',
            'domain' => 'Domain',
            'roles-permissions' => 'Roles & Permissions',
            'security' => 'Security',
            'quiz-results' => 'Quiz Results',
            'student-assignments' => 'Assignments',
            'results' => 'Results',
            'student-progress' => 'Student Progress',
        ];

        $title = $labels[$feature] ?? Str::title(str_replace('-', ' ', $feature));

        return Inertia::render('Tenant/ComingSoon', [
            'title' => $title,
        ]);
    }
}
