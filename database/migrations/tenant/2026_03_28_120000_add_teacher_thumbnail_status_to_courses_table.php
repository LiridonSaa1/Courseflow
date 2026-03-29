<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('description')->constrained('teachers')->nullOnDelete();
            $table->string('thumbnail')->nullable()->after('teacher_id');
            $table->string('status', 32)->default('draft')->after('thumbnail');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'thumbnail', 'status']);
        });
    }
};
