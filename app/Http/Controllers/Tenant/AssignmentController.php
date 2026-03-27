<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentController extends Controller
{
    public function create(Request $request, Assignment $assignment): Response
    {
        return Inertia::render('Tenant/Assignments/Submit', ['assignment' => $assignment->load('lesson')]);
    }

    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string']]);
        Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'status' => $assignment->requires_manual_grade ? 'pending_grade' : 'submitted',
        ]);

        return redirect()->route('tenant.dashboard')->with('success', 'Assignment submitted.');
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'teacher_feedback' => ['nullable', 'string'],
        ]);
        $submission->update([
            'score' => $data['score'],
            'teacher_feedback' => $data['teacher_feedback'] ?? null,
            'status' => 'graded',
        ]);

        return back();
    }
}
