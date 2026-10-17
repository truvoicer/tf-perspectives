<?php
// database/migrations/2024_01_01_000000_create_perspectives_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perspectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                  ->constrained('perspectives')->cascadeOnDelete();
            $table->foreignId('root_id')->nullable()
                  ->constrained('perspectives')->cascadeOnDelete();

            $table->string('voice', 120)->nullable(); // "As a night-shift nurse"
            $table->text('body');                     // what the world looks like from here
            $table->unsignedTinyInteger('depth')->default(0);

            $table->timestamps();

            $table->index(['root_id', 'depth']);
        });

        Schema::create('perspective_empathies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perspective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['perspective_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perspective_empathies');
        Schema::dropIfExists('perspectives');
    }
};
