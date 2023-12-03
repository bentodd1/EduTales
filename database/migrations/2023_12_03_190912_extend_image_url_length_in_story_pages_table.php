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
        Schema::table('story_pages', function (Blueprint $table) {
            $table->text('image_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('story_pages', function (Blueprint $table) {
            $table->string('image_url', 255)->change(); // Assuming it was a VARCHAR(255)
        });
    }
};
