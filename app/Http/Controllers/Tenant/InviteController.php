<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InviteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['owner', 'teacher']), 403);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $password = Str::password(12);
        $role = Role::query()->firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        if (User::query()->where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => __('User already exists. Add them from the student list.')]);
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
        ]);
        $user->assignRole($role);
        Student::query()->create([
            'user_id' => $user->id,
            'invite_token' => Str::random(40),
            'invited_at' => now(),
        ]);

        $host = request()->getHost();
        Mail::raw(
            "You've been invited to {$host}. Email: {$data['email']}\nTemporary password: {$password}\nPlease log in and change your password.",
            function ($m) use ($data) {
                $m->to($data['email'])->subject('You have been invited');
            }
        );

        return back();
    }
}
