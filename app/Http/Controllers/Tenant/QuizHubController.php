<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizHubController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $q = Quiz::query()
            ->with(['lesson.module.course'])
            ->orderBy('id');
        $tid = $this->staffTeacherId($request);
        if ($tid !== null) {
            $q->whereHas('lesson.module.course', fn ($cq) => $cq->where('teacher_id', $tid));
        }
        $quizzes = $q->get();

        return Inertia::render('Tenant/Quizzes/Hub', [
            'quizzes' => $quizzes,
        ]);
    }
}
