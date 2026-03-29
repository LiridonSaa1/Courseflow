<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
            $table->string('profile_photo')->nullable()->after('title');
            $table->string('status', 32)->default('active')->after('profile_photo');
            $table->softDeletes()->after('updated_at');
        });

        $rows = DB::table('teachers')->select('id', 'user_id')->get();
        foreach ($rows as $row) {
            $user = DB::table('users')->where('id', $row->user_id)->first();
            if (! $user || ! isset($user->name)) {
                continue;
            }
            $name = trim((string) $user->name);
            if ($name === '') {
                continue;
            }
            $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY);
            $first = $parts[0] ?? '';
            $last = $parts[1] ?? '';
            DB::table('teachers')->where('id', $row->id)->update([
                'first_name' => $first !== '' ? $first : null,
                'last_name' => $last !== '' ? $last : null,
            ]);
        }

        DB::table('teachers')->whereNull('status')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'profile_photo',
                'status',
            ]);
        });
    }
};
