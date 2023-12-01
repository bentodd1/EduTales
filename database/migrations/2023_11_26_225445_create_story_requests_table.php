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
        Schema::create('story_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained();
            $table->string('subject');
            $table->text('description');
            $table->unsignedInteger('page_number');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_requests');
    }
};
