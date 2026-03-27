<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->staff($request);
        $students = Student::query()->with('user')->latest()->get();

        return Inertia::render('Tenant/Students/Index', ['students' => $students]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = Role::query()->firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->assignRole($role);
        Student::query()->create(['user_id' => $user->id]);

        return back();
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->staff($request);
        $student->user?->delete();

        return back();
    }

    protected function staff(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);
    }
}
