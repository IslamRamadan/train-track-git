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
        Schema::create('coach_exercise_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191)->nullable();
            $table->string('description', 295)->nullable();
            $table->unsignedBigInteger('coach_id');
            $table->foreign('coach_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_templates');
    }
};
