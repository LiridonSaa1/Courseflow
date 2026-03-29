<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
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
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $this->staff($request);
        $tid = $this->staffTeacherId($request);
        $sessionsQuery = LiveSession::query()->with('studyClass')->latest();
        $classesQuery = StudyClass::query()->with('course');
        if ($tid !== null) {
            $sessionsQuery->whereHas('studyClass', fn ($q) => $q->where('teacher_id', $tid));
            $classesQuery->where('teacher_id', $tid);
        }
        $sessions = $sessionsQuery->get();
        $classes = $classesQuery->get();

        return Inertia::render('Tenant/LiveSessions/Index', [
            'sessions' => $sessions,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'starts_at' => ['nullable', 'date'],
            'meeting_url' => ['nullable', 'url'],
        ]);
        if (($tid = $this->staffTeacherId($request)) !== null && ! empty($data['class_id'])) {
            abort_unless(
                StudyClass::query()->whereKey($data['class_id'])->where('teacher_id', $tid)->exists(),
                403
            );
        }
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
