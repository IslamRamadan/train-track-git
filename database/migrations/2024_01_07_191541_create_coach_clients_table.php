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
        Schema::create('coach_clients', function (Blueprint $table) {
            $table->id();
            $table->foreign('coach_id')->references('id')->on('users');
            $table->unsignedBigInteger('coach_id');
            $table->foreign('client_id')->references('id')->on('users');
            $table->unsignedBigInteger('client_id');
            $table->enum('status', ["0", "1", "2"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_clients');
    }
};
