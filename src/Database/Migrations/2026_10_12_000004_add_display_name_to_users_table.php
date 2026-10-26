<?php
// packages/truvoicer/tf-perspectives/database/migrations/2025_01_01_000004_add_display_name_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'display_name')) {
            return;
        }

        // 1. Add nullable first so we can backfill before enforcing NOT NULL + unique.
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name', 60)->nullable()->after('last_name');
        });

        // 2. Backfill from name, guaranteeing uniqueness with a -N suffix.
        DB::table('users')
            ->select('id', 'first_name', 'last_name')
            ->orderBy('id')
            ->chunk(200, function ($users) {
                foreach ($users as $user) {
                    $base = Str::slug(($user->first_name ?? '').' '.($user->last_name ?? ''));

                    if ($base === '') {
                        $base = 'user-'.$user->id;
                    }

                    $candidate = $base;
                    $suffix = 1;

                    while (
                        DB::table('users')
                            ->where('display_name', $candidate)
                            ->where('id', '!=', $user->id)
                            ->exists()
                    ) {
                        $candidate = $base.'-'.(++$suffix);
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['display_name' => $candidate]);
                }
            });

        // 3. Enforce NOT NULL + unique now that every row has a value.
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name', 60)->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'display_name')) {
                try {
                    $table->dropUnique('users_display_name_unique');
                } catch (\Throwable $e) {
                    // Index didn't exist under that name — ignore.
                }

                $table->dropColumn('display_name');
            }
        });
    }
};
