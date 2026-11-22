<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovation_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('innovation_program_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innovation_program_id')->constrained('innovation_programs')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['innovation_program_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovation_program_items');
        Schema::dropIfExists('innovation_programs');
    }
};
