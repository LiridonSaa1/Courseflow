<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $this->tenantAdmin($request);

        $teachers = Teacher::query()
            ->with('user')
            ->latest()
            ->get();

        return Inertia::render('Tenant/Teachers/Index', [
            'teachers' => $teachers,
            'archivedTeachersCount' => Teacher::onlyTrashed()->count(),
        ]);
    }

    public function archive(Request $request): Response
    {
        $this->tenantAdmin($request);

        $teachers = Teacher::onlyTrashed()
            ->with('user')
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Tenant/Teachers/Archive', [
            'teachers' => $teachers,
        ]);
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $this->tenantAdmin($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:teachers,id'],
        ])['ids'];

        Teacher::query()->whereIn('id', $ids)->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Mësimdhënësit u zhvendosën në arkiv.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $this->tenantAdmin($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('teachers', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        Teacher::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()
            ->route('teachers.archive')
            ->with('success', 'Mësimdhënësit u rikthyen te lista kryesore.');
    }

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $this->tenantAdmin($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('teachers', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $teachers = Teacher::onlyTrashed()->whereIn('id', $ids)->get();
        foreach ($teachers as $teacher) {
            $this->deleteStoredProfilePhoto($teacher->profile_photo);
            $userId = $teacher->user_id;
            $teacher->forceDelete();
            User::query()->where('id', $userId)->delete();
        }

        return redirect()
            ->route('teachers.archive')
            ->with('success', 'Mësimdhënësit u fshinë përgjithmonë.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->tenantAdmin($request);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'string', 'max:32', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $role = Role::query()->firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $user = User::query()->create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->assignRole($role);

        $profilePath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePath = $this->storeProfilePhotoFile($request->file('profile_photo'));
        }

        Teacher::query()->create([
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'title' => $data['title'] ?? null,
            'profile_photo' => $profilePath,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Mësimdhënësi u krijua.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->tenantAdmin($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone' => ['nullable', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'string', 'max:32', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $teacher->user?->update([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
        ]);

        $updates = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'title' => $data['title'] ?? null,
            'status' => $data['status'],
        ];

        if ($request->hasFile('profile_photo')) {
            $this->deleteStoredProfilePhoto($teacher->profile_photo);
            $updates['profile_photo'] = $this->storeProfilePhotoFile($request->file('profile_photo'));
        }

        $teacher->update($updates);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Mësimdhënësi u përditësua.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->tenantAdmin($request);
        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Mësimdhënësi u zhvendos në arkiv.');
    }

    protected function storeProfilePhotoFile(UploadedFile $file): string
    {
        return $file->store('teacher-photos', 'public');
    }

    protected function deleteStoredProfilePhoto(?string $stored): void
    {
        if ($stored === null || $stored === '') {
            return;
        }
        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            return;
        }
        $path = $stored;
        if (str_starts_with($path, '/storage/')) {
            $path = ltrim(substr($path, strlen('/storage/')), '/');
        }
        if ($path !== '' && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }
}
