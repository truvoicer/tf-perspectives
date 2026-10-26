<?php
// packages/truvoicer/tf-perspectives/database/migrations/2025_01_01_000007_add_auth_features_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'provider_name')) {
                $table->string('provider_name', 32)->nullable()->after('remember_token');
                $table->string('provider_id', 255)->nullable()->after('provider_name');
                $table->index(['provider_name', 'provider_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider_name', 'provider_id']);
        });
    }
};
