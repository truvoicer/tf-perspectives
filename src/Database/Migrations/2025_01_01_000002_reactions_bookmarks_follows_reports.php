<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // .../2025_01_01_000002_reactions_bookmarks_follows_reports.php
        Schema::create('reactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type', 16);            // empathy | insight | relate | curious
            $t->timestamps();
            $t->unique(['perspective_id', 'user_id', 'type']);
        });

        Schema::create('bookmarks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['perspective_id', 'user_id']);
        });

        Schema::create('follows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('followed_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['follower_id', 'followed_id']);
        });

        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('reason', 32);
            $t->text('notes')->nullable();
            $t->string('status', 16)->default('pending');
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('follows');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('reactions');
    }
};
