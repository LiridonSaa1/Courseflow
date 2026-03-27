<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discussion;
use App\Models\DiscussionMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function index(Request $request): Response
    {
        $discussions = Discussion::query()->with(['user', 'discussable'])->latest()->take(50)->get();

        return Inertia::render('Tenant/Community/Index', [
            'discussions' => $discussions,
            'courses' => Course::query()->select('id', 'title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);
        $course = Course::query()->findOrFail($data['course_id']);

        Discussion::query()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'user_id' => $request->user()->id,
            'discussable_type' => Course::class,
            'discussable_id' => $course->id,
        ]);

        return back();
    }

    public function reply(Request $request, Discussion $discussion): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string']]);
        DiscussionMessage::query()->create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back();
    }
}
