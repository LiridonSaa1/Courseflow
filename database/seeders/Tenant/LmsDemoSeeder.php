<?php

namespace Database\Seeders\Tenant;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class LmsDemoSeeder extends Seeder
{
    /**
     * Seed demo teachers and courses in the tenant database.
     * Requires at least one tenant user (e.g. owner from signup) for created_by.
     */
    public function run(): void
    {
        foreach (['owner', 'admin', 'teacher', 'student'] as $roleName) {
            Role::query()->firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
            );
        }

        $creator = User::query()->first();
        if (! $creator) {
            $this->command?->warn('LmsDemoSeeder: no users in tenant DB; skipping teachers and courses.');

            return;
        }

        $teacherProfiles = [
            ['name' => 'Ana Hoxha', 'email' => 'teacher1@tenant.local', 'title' => 'Lead instructor'],
            ['name' => 'Dardan Krasniqi', 'email' => 'teacher2@tenant.local', 'title' => 'Conversation coach'],
            ['name' => 'Elira Berisha', 'email' => 'teacher3@tenant.local', 'title' => 'Grammar specialist'],
            ['name' => 'Fisnik Morina', 'email' => 'teacher4@tenant.local', 'title' => 'Exam preparation'],
        ];

        $teachers = [];
        foreach ($teacherProfiles as $profile) {
            $user = User::query()->updateOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make('password'),
                ],
            );
            if (! $user->hasRole('teacher')) {
                $user->assignRole('teacher');
            }
            $name = trim($profile['name']);
            $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY);
            $firstName = $parts[0] ?? $name;
            $lastName = $parts[1] ?? '';
            $teachers[] = Teacher::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'title' => $profile['title'],
                    'first_name' => $firstName,
                    'last_name' => $lastName !== '' ? $lastName : null,
                    'status' => 'active',
                ]
            );
        }

        $teacherCount = count($teachers);

        $courses = [
            [
                'title' => 'English Conversation A2',
                'language' => 'English',
                'level' => 'A2',
                'description' => 'Practical speaking and listening for everyday situations.',
                'status' => 'published',
                'thumbnail' => null,
                'teacher_index' => 0,
            ],
            [
                'title' => 'Business English B1',
                'language' => 'English',
                'level' => 'B1',
                'description' => 'Meetings, emails, and presentations at work.',
                'status' => 'published',
                'thumbnail' => null,
                'teacher_index' => 1,
            ],
            [
                'title' => 'Italian for Beginners',
                'language' => 'Italian',
                'level' => 'A1',
                'description' => 'Pronunciation, essential phrases, and basic grammar.',
                'status' => 'draft',
                'thumbnail' => null,
                'teacher_index' => 2,
            ],
            [
                'title' => 'German Grammar B2',
                'language' => 'German',
                'level' => 'B2',
                'description' => 'Cases, word order, and complex sentences.',
                'status' => 'published',
                'thumbnail' => null,
                'teacher_index' => 3,
            ],
            [
                'title' => 'French Listening C1',
                'language' => 'French',
                'level' => 'C1',
                'description' => 'Podcasts, news, and accent training.',
                'status' => 'draft',
                'thumbnail' => null,
                'teacher_index' => 0,
            ],
            [
                'title' => 'Spanish Travel A2',
                'language' => 'Spanish',
                'level' => 'A2',
                'description' => 'Hotels, directions, food, and small talk on the road.',
                'status' => 'published',
                'thumbnail' => null,
                'teacher_index' => 1,
            ],
            [
                'title' => 'IELTS Writing Intensive',
                'language' => 'English',
                'level' => 'B2',
                'description' => 'Task 1 and Task 2 strategies with timed practice.',
                'status' => 'published',
                'thumbnail' => null,
                'teacher_index' => 3,
            ],
        ];

        foreach ($courses as $row) {
            $idx = (int) ($row['teacher_index'] ?? 0);
            unset($row['teacher_index']);

            $row['teacher_id'] = $teachers[$idx % $teacherCount]->id;
            $row['created_by'] = $creator->id;

            Course::query()->updateOrCreate(
                [
                    'title' => $row['title'],
                    'language' => $row['language'],
                ],
                $row
            );
        }
    }
}
