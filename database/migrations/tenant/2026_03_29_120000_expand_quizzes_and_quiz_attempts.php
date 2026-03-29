<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->text('description')->nullable()->after('title');
            $table->string('type', 32)->default('lesson')->after('description');
            $table->unsignedSmallInteger('pass_mark')->nullable()->after('time_limit_seconds');
            $table->unsignedSmallInteger('total_marks')->nullable()->after('pass_mark');
            $table->unsignedSmallInteger('max_attempts')->nullable()->after('total_marks');
            $table->boolean('is_shuffle_questions')->default(false)->after('max_attempts');
            $table->boolean('is_shuffle_answers')->default(false)->after('is_shuffle_questions');
            $table->boolean('show_results_instantly')->default(true)->after('is_shuffle_answers');
            $table->boolean('allow_retry')->default(true)->after('show_results_instantly');
            $table->boolean('negative_marking')->default(false)->after('allow_retry');
            $table->boolean('show_correct_after_finish')->default(true)->after('negative_marking');
            $table->string('status', 32)->default('draft')->after('show_correct_after_finish');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_at');
        });

        $this->backfillQuizzes();

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('user_id');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->boolean('passed')->nullable()->after('max_score');
            $table->json('result_summary')->nullable()->after('answers');
        });

        $this->backfillQuizAttempts();
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'completed_at', 'passed', 'result_summary']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'course_id',
                'description',
                'type',
                'pass_mark',
                'total_marks',
                'max_attempts',
                'is_shuffle_questions',
                'is_shuffle_answers',
                'show_results_instantly',
                'allow_retry',
                'negative_marking',
                'show_correct_after_finish',
                'status',
                'published_at',
                'created_by',
                'updated_by',
            ]);
        });
    }

    private function backfillQuizzes(): void
    {
        if (! Schema::hasTable('quizzes')) {
            return;
        }

        DB::table('quizzes')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $courseId = null;
                if (! empty($row->lesson_id)) {
                    $courseId = DB::table('lessons')
                        ->join('modules', 'lessons.module_id', '=', 'modules.id')
                        ->where('lessons.id', $row->lesson_id)
                        ->value('modules.course_id');
                }

                $createdBy = null;
                if (! empty($row->lesson_id)) {
                    $createdBy = DB::table('lessons')->where('id', $row->lesson_id)->value('created_by');
                }

                $totalMarks = DB::table('questions')->where('quiz_id', $row->id)->sum('points');

                DB::table('quizzes')->where('id', $row->id)->update([
                    'course_id' => $courseId,
                    'is_shuffle_questions' => (bool) ($row->randomize ?? false),
                    'status' => 'published',
                    'published_at' => $row->updated_at ?? $row->created_at ?? now(),
                    'created_by' => $createdBy,
                    'total_marks' => $totalMarks > 0 ? (int) $totalMarks : null,
                    'pass_mark' => 50,
                ]);
            }
        });
    }

    private function backfillQuizAttempts(): void
    {
        if (! Schema::hasTable('quiz_attempts')) {
            return;
        }

        DB::table('quiz_attempts')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $submitted = $row->submitted_at ?? $row->updated_at ?? $row->created_at;
                $passMark = null;
                if (! empty($row->quiz_id)) {
                    $passMark = DB::table('quizzes')->where('id', $row->quiz_id)->value('pass_mark');
                }
                $max = (int) ($row->max_score ?? 0);
                $score = (int) ($row->score ?? 0);
                $percent = $max > 0 ? (int) round($score / $max * 100) : 0;
                $passed = $passMark !== null ? ($percent >= (int) $passMark) : null;

                DB::table('quiz_attempts')->where('id', $row->id)->update([
                    'started_at' => $row->created_at,
                    'completed_at' => $submitted,
                    'passed' => $row->status === 'submitted' ? $passed : null,
                ]);
            }
        });
    }
};
