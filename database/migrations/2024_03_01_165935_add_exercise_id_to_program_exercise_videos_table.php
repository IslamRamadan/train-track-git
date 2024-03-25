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
        Schema::table('program_exercise_videos', function (Blueprint $table) {
            $table->unsignedBigInteger('program_exercise_id');
            $table->foreign('program_exercise_id')->references('id')->on('program_exercises');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_exercise_videos', function (Blueprint $table) {
            //
        });
    }
};
