<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // sejarah | visi-misi
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable(); // Sejarah: narasi. Visi-Misi: teks visi.
            $table->text('mission')->nullable(); // hanya Visi-Misi, satu misi per baris
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('profile_page_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_page_id')->constrained('profile_pages')->cascadeOnDelete();
            $table->string('image');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['profile_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_page_photos');
        Schema::dropIfExists('profile_pages');
    }
};
