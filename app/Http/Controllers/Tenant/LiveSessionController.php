<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LiveSession;
use App\Models\StudyClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LiveSessionController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
        $sessions = LiveSession::query()->with('studyClass')->latest()->get();
        $classes = StudyClass::query()->with('course')->get();

        return Inertia::render('Tenant/LiveSessions/Index', [
            'sessions' => $sessions,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'starts_at' => ['nullable', 'date'],
            'meeting_url' => ['nullable', 'url'],
        ]);
        $data['join_token'] = Str::uuid()->toString();
        LiveSession::query()->create($data);

        return back();
    }

    public function join(Request $request, LiveSession $liveSession): RedirectResponse
    {
        Attendance::query()->firstOrCreate(
            ['live_session_id' => $liveSession->id, 'user_id' => $request->user()->id],
            ['status' => 'attended']
        );

        $url = $liveSession->meeting_url ?? route('tenant.dashboard');

        return redirect()->away($url);
    }
}
