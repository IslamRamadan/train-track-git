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
        Schema::create('gym_coaches', function (Blueprint $table) {
            $table->id();
            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->unsignedBigInteger('gym_id')->nullable();
            $table->foreign('coach_id')->references('id')->on('users');
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->enum('privilege', ['1', '2', '3'])->default('1');//1 owner ,2 admin ,3 coach
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_coaches');
    }
};
