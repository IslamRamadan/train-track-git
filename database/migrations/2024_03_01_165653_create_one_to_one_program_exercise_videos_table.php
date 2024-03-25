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
        Schema::create('oto_program_exercise_videos', function (Blueprint $table) {
            $table->id();
            $table->string('link', 191);
            $table->string('title', 191);
            $table->unsignedBigInteger('oto_program_exercise_id');
            $table->foreign('oto_program_exercise_id')->references('id')->on('one_to_one_program_exercises');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_program_exercises_videos');
    }
};
