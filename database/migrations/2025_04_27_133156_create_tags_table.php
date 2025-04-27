<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6B7280');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure tag names are unique per project
            $table->unique(['name', 'project_id']);
        });

        Schema::create('item_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['item_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_tag');
        Schema::dropIfExists('tags');
    }
};
