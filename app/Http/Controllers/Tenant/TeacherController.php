<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    public function index(Request $request): Response
    {
        $this->owner($request);
        $teachers = Teacher::query()->with('user')->latest()->get();

        return Inertia::render('Tenant/Teachers/Index', ['teachers' => $teachers]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->owner($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::query()->firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->assignRole($role);
        Teacher::query()->create(['user_id' => $user->id, 'title' => $data['title'] ?? null]);

        return back();
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->owner($request);
        $teacher->user?->delete();

        return back();
    }

    protected function owner(Request $request): void
    {
        abort_unless($request->user()->hasRole('owner'), 403);
    }
}
