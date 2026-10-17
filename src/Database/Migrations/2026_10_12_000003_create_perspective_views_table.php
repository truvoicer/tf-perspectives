<?php
// packages/truvoicer/tf-perspectives/database/migrations/2025_01_01_000003_create_perspective_views_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perspective_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            // Dedup lookups ("has this viewer seen this today?").
            $table->index(['perspective_id', 'user_id', 'created_at']);
            $table->index(['perspective_id', 'ip_hash', 'created_at']);

            // Dashboard aggregation ("how many views across my perspectives
            // this week?"), ordered by perspective then date.
            $table->index(['perspective_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perspective_views');
    }
};
