<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Mail\StudentInvited;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

class InviteController extends Controller
{
    use AuthorizesTenantRoles;

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $password = Str::password(12);
        $role = Role::query()->firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        if (User::query()->where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => __('User already exists. Add them from the student list.')]);
        }

        $name = trim($data['name']);
        $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY);
        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? '';

        $user = User::query()->create([
            'name' => $name,
            'email' => $data['email'],
            'password' => $password,
        ]);
        $user->assignRole($role);
        Student::query()->create([
            'user_id' => $user->id,
            'invite_token' => Str::random(40),
            'invited_at' => now(),
            'first_name' => $firstName,
            'last_name' => $lastName !== '' ? $lastName : null,
            'status' => 'pending',
        ]);

        $workspaceName = (string) (tenant('course_name') ?? tenant('id') ?? config('app.name'));
        $loginUrl = url('/login');

        try {
            Mail::to($data['email'])->send(new StudentInvited(
                recipientName: $name,
                inviteeEmail: $data['email'],
                loginUrl: $loginUrl,
                temporaryPassword: $password,
                workspaceName: $workspaceName,
            ));
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'warning',
                __('Student was created, but the invitation email could not be sent. Add Brevo SMTP to .env or share the password manually.'),
            );
        }

        return back()->with('success', __('Invitation sent to :email.', ['email' => $data['email']]));
    }
}
