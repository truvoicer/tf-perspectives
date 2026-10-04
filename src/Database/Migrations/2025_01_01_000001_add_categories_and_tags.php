<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // packages/truvoicer/tf-perspectives/database/migrations/2025_01_01_000001_add_categories_and_tags.php
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('icon')->default('lightbulb');
            $t->string('color', 16)->default('#2f5d50');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });

        Schema::create('perspective_tag', function (Blueprint $t) {
            $t->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['perspective_id', 'tag_id']);
        });

        Schema::table('perspectives', function (Blueprint $t) {
            $t->foreignId('category_id')->nullable()->after('user_id')
                ->constrained()->nullOnDelete();
            $t->string('mood', 32)->nullable()->after('voice');
            $t->boolean('is_anonymous')->default(false)->after('mood');
        });
    }

    public function down(): void
    {
        Schema::table('perspectives', function (Blueprint $t) {
            $t->dropForeign(['category_id']);
            $t->dropColumn('category_id');
            $t->dropColumn('mood');
            $t->dropColumn('is_anonymous');
        });

        Schema::dropIfExists('perspective_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
