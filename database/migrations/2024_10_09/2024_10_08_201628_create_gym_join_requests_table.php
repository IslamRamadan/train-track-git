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
        Schema::create('gym_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->unsignedBigInteger('gym_id')->nullable();
            $table->foreign('coach_id')->references('id')->on('users');
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('users');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->enum('status', ['0', '1', '2'])->default('1');//0 rejected ,1 pending ,2 accepted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_join_requests');
    }
};
