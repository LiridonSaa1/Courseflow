<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SpeakingAttempt;
use App\Services\SpeakingScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpeakingController extends Controller
{
    public function store(Request $request, Lesson $lesson, SpeakingScoreService $scores): RedirectResponse
    {
        $request->validate([
            'audio' => ['nullable', 'file'],
            'transcript' => ['nullable', 'string'],
        ]);

        $path = null;
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('speaking', ['disk' => 'public']);
        }

        $reference = is_array($lesson->grammar)
            ? ($lesson->grammar['reference'] ?? $lesson->title)
            : (string) $lesson->title;

        $transcript = $request->input('transcript');
        $result = $scores->score((string) $reference, $transcript ? (string) $transcript : null);

        SpeakingAttempt::query()->create([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
            'audio_path' => $path,
            'transcript' => $transcript,
            'reference_text' => (string) $reference,
            'scores' => $result,
        ]);

        return back()->with('success', 'Speaking attempt recorded.');
    }
}
