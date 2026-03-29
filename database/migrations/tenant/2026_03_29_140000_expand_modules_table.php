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
        Schema::table('modules', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('status', 32)->default('draft')->after('sort_order');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        DB::table('modules')->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['description', 'status', 'published_at', 'created_by', 'updated_by']);
        });
    }
};
