<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('title');
            $table->text('short_description')->nullable()->after('slug');
            $table->longText('description')->nullable()->after('short_description');
            $table->string('level', 8)->default('A1')->after('type');
            $table->string('thumbnail', 2048)->nullable()->after('level');
            $table->string('video_url', 2048)->nullable()->after('thumbnail');
            $table->string('audio_url', 2048)->nullable()->after('video_url');
            $table->longText('transcript')->nullable()->after('audio_url');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('transcript');
            $table->boolean('is_free_preview')->default(false)->after('duration_minutes');
            $table->boolean('is_downloadable')->default(false)->after('is_free_preview');
            $table->boolean('requires_completion')->default(false)->after('is_downloadable');
            $table->string('status', 32)->default('draft')->after('requires_completion');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_at');
        });

        $this->backfillLessonSlugs();

        Schema::table('lessons', function (Blueprint $table) {
            $table->unique(['module_id', 'slug']);
        });

        Schema::create('lesson_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 32);
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('file_url', 2048)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_url', 2048)->nullable();
            $table->string('type', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('lesson_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        Schema::table('student_progress', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('lesson_id');
            $table->string('last_position', 512)->nullable()->after('percent');
            $table->string('state', 32)->default('not_started')->after('last_position');
        });

        DB::table('student_progress')->whereNotNull('completed_at')->update(['state' => 'completed']);
        DB::table('student_progress')
            ->whereNull('completed_at')
            ->where('percent', '>', 0)
            ->update(['state' => 'in_progress']);

        $this->migrateLessonJsonToContents();
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'last_position', 'state']);
        });

        Schema::dropIfExists('lesson_notes');
        Schema::dropIfExists('lesson_resources');
        Schema::dropIfExists('lesson_contents');

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropUnique(['module_id', 'slug']);
            $table->dropColumn([
                'slug',
                'short_description',
                'description',
                'level',
                'thumbnail',
                'video_url',
                'audio_url',
                'transcript',
                'duration_minutes',
                'is_free_preview',
                'is_downloadable',
                'requires_completion',
                'status',
                'published_at',
                'created_by',
                'updated_by',
                'deleted_at',
            ]);
        });
    }

    private function backfillLessonSlugs(): void
    {
        $lessons = DB::table('lessons')->orderBy('id')->get();
        $seen = [];

        foreach ($lessons as $l) {
            $base = Str::slug((string) $l->title) ?: 'lesson';
            $slug = $base;
            $i = 1;
            $key = $l->module_id.'|'.$slug;
            while (isset($seen[$key])) {
                $slug = $base.'-'.$i++;
                $key = $l->module_id.'|'.$slug;
            }
            $seen[$key] = true;

            DB::table('lessons')->where('id', $l->id)->update(['slug' => $slug]);
        }
    }

    private function migrateLessonJsonToContents(): void
    {
        $lessons = DB::table('lessons')->orderBy('id')->get();

        foreach ($lessons as $l) {
            $order = 0;
            $content = json_decode($l->content ?? 'null', true);
            if (is_array($content)) {
                if (! empty($content['body'])) {
                    DB::table('lesson_contents')->insert([
                        'lesson_id' => $l->id,
                        'content_type' => 'text',
                        'title' => null,
                        'content' => is_string($content['body']) ? $content['body'] : json_encode($content['body']),
                        'file_url' => null,
                        'sort_order' => $order++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $examples = json_decode($l->examples ?? 'null', true);
            if (is_array($examples)) {
                foreach ($examples as $ex) {
                    DB::table('lesson_contents')->insert([
                        'lesson_id' => $l->id,
                        'content_type' => 'example',
                        'title' => null,
                        'content' => is_string($ex) ? $ex : json_encode($ex),
                        'file_url' => null,
                        'sort_order' => $order++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
