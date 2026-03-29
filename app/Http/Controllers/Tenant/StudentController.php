<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\AuthorizesTenantRoles;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    use AuthorizesTenantRoles;

    public function index(Request $request): Response
    {
        $this->staff($request);

        $students = $this->scopeStudentsForStaff($request, Student::query())
            ->with('user')
            ->latest()
            ->get();

        $archivedScope = $this->scopeStudentsForStaff($request, Student::onlyTrashed());

        return Inertia::render('Tenant/Students/Index', [
            'students' => $students,
            'archivedStudentsCount' => $archivedScope->count(),
        ]);
    }

    public function archive(Request $request): Response
    {
        $this->staff($request);

        $students = $this->scopeStudentsForStaff($request, Student::onlyTrashed())
            ->with('user')
            ->latest('deleted_at')
            ->get();

        return Inertia::render('Tenant/Students/Archive', [
            'students' => $students,
        ]);
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:students,id'],
        ])['ids'];

        $unique = array_unique($ids);
        $deleted = $this->scopeStudentsForStaff($request, Student::query())->whereIn('id', $unique)->count();
        abort_unless($deleted === count($unique), 403);

        $this->scopeStudentsForStaff($request, Student::query())->whereIn('id', $ids)->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Studentët u zhvendosën në arkiv.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('students', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $unique = array_unique($ids);
        abort_unless(
            $this->scopeStudentsForStaff($request, Student::onlyTrashed())->whereIn('id', $unique)->count() === count($unique),
            403
        );
        $this->scopeStudentsForStaff($request, Student::onlyTrashed())->whereIn('id', $ids)->restore();

        return redirect()
            ->route('students.archive')
            ->with('success', 'Studentët u rikthyen te lista kryesore.');
    }

    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $this->staff($request);
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('students', 'id')->whereNotNull('deleted_at'),
            ],
        ])['ids'];

        $unique = array_unique($ids);
        $students = $this->scopeStudentsForStaff($request, Student::onlyTrashed())
            ->whereIn('id', $ids)
            ->get();
        abort_unless($students->count() === count($unique), 403);
        foreach ($students as $student) {
            $this->deleteStoredProfilePhoto($student->profile_photo);
            $userId = $student->user_id;
            $student->forceDelete();
            User::query()->where('id', $userId)->delete();
        }

        return redirect()
            ->route('students.archive')
            ->with('success', 'Studentët u fshinë përgjithmonë.');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->staff($request);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:64'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:32', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'string', 'max:32', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $role = Role::query()->firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
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

        Student::query()->create([
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'profile_photo' => $profilePath,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Studenti u krijua.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffStudent($request, $student);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student->user_id)],
            'phone' => ['nullable', 'string', 'max:64'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:32', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'string', 'max:32', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $student->user?->update([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
        ]);

        $updates = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'status' => $data['status'],
        ];

        if ($request->hasFile('profile_photo')) {
            $this->deleteStoredProfilePhoto($student->profile_photo);
            $updates['profile_photo'] = $this->storeProfilePhotoFile($request->file('profile_photo'));
        }

        $student->update($updates);

        return redirect()
            ->route('students.index')
            ->with('success', 'Studenti u përditësua.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->staff($request);
        $this->authorizeStaffStudent($request, $student);
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Studenti u zhvendos në arkiv.');
    }

    protected function storeProfilePhotoFile(UploadedFile $file): string
    {
        return $file->store('student-photos', 'public');
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
