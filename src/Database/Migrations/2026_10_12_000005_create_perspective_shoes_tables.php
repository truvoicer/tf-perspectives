<?php
// packages/truvoicer/tf-perspectives/database/migrations/2025_01_01_000005_create_perspective_shoes_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Shoes: someone stepping into a different pair, anchored to a
        //    perspective. Not a reply, not a rebuttal — a position.
        Schema::create('perspective_shoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // The position the author is standing in.
            // e.g. "As a neighbour with a disability", "As a bus driver"
            $table->string('voice', 120);

            // What the world looks like from inside that position.
            $table->text('body');

            // Optional tone marker, same domain as perspectives.mood.
            $table->string('mood', 32)->nullable();

            // Same idea as perspectives.is_anonymous — a shoe can be posted
            // anonymously if the position is sensitive.
            $table->boolean('is_anonymous')->default(false);

            // Reserved for the ranking / "changed my view" feature you
            // mentioned. Null means the perspective author hasn't marked it
            // yet; the values are meaningful only once that feature ships.
            $table->unsignedTinyInteger('impact_rank')->nullable();
            $table->boolean('changed_my_view')->default(false);
            $table->timestamp('marked_by_author_at')->nullable();

            // Denormalised counters. Kept fresh by observers on the
            // shoe_discussions and shoe_empathies tables (when those exist).
            $table->unsignedInteger('discussions_count')->default(0);
            $table->unsignedInteger('empathies_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // The primary access pattern: "all shoes for this perspective,
            // most recent first" / "highest ranked first" once ranking ships.
            $table->index(['perspective_id', 'created_at']);
            $table->index(['perspective_id', 'impact_rank']);

            // Author profile: "shoes this user has stepped into".
            $table->index(['user_id', 'created_at']);
        });

        // ── Discussions: a threaded conversation attached to a single shoe.
        //    Self-referential so replies to replies work naturally, without
        //    the "which perspective is this attached to" ambiguity that
        //    reusing the perspectives table would introduce.
        Schema::create('shoe_discussions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shoe_id')->constrained('perspective_shoes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Null for a top-level discussion on a shoe; set for replies.
            $table->foreignId('parent_id')->nullable()
                ->constrained('shoe_discussions')->cascadeOnDelete();

            $table->text('body');
            $table->boolean('is_anonymous')->default(false);

            // Depth lets you cap nesting or render indentation without
            // walking the tree on every read.
            $table->unsignedTinyInteger('depth')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // All discussions on a shoe, threaded by parent.
            $table->index(['shoe_id', 'parent_id', 'created_at']);

            // "My discussions" and moderation.
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoe_discussions');
        Schema::dropIfExists('perspective_shoes');
    }
};
