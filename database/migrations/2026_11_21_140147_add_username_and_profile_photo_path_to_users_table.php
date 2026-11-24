<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
            $table->string('profile_photo_path')->nullable()->after('username');
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $baseUsername = strtolower(Str::ascii((string) $user->name));
                    $baseUsername = preg_replace('/[^a-z0-9._]+/', '_', $baseUsername) ?? '';
                    $baseUsername = trim(substr($baseUsername, 0, 30), '._');
                    $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
                    $username = $baseUsername;
                    $suffix = 1;

                    while (DB::table('users')->where('username', $username)->exists()) {
                        $suffix++;
                        $suffixText = '_'.$suffix;
                        $username = substr($baseUsername, 0, 30 - strlen($suffixText)).$suffixText;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $username]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'profile_photo_path']);
        });
    }
};
