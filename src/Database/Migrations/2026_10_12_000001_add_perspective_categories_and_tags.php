<?php
// .../database/migrations/2025_01_01_000001_create_categories_and_tags_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perspective_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 64)->default('lightbulb');
            $table->string('color', 16)->default('#2f5d50');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->timestamps();
        });

        Schema::create('perspective_tag', function (Blueprint $table) {
            $table->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['perspective_id', 'tag_id']);
        });

        Schema::table('perspectives', function (Blueprint $table) {
            if (! Schema::hasColumn('perspectives', 'perspective_category_id')) {
                $table->foreignId('perspective_category_id')->nullable()->after('user_id')
                      ->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('perspectives', 'mood')) {
                $table->string('mood', 32)->nullable()->after('voice');
            }
            if (! Schema::hasColumn('perspectives', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('mood');
            }
            if (! Schema::hasColumn('perspectives', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_anonymous');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perspectives', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perspective_category_id');
            $table->dropColumn(['mood', 'is_anonymous', 'is_featured']);
        });
        Schema::dropIfExists('perspective_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('perspective_categories');
    }
};
