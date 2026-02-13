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
        Schema::create('exercise_log_videos', function (Blueprint $table) {
            $table->id();
            $table->string('path', 191);
            $table->unsignedBigInteger('exercise_log_id')->nullable();
            $table->foreign('exercise_log_id')->references('id')->on('exercise_logs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_log_videos');
    }
};
