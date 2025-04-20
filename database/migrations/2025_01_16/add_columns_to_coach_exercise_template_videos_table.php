<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coach_exercise_template_videos', function (Blueprint $table) {
            $table->string('title', 191)->nullable();
            $table->string('link', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coach_exercise_template_videos', function (Blueprint $table) {

        });
    }
};
