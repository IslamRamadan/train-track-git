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
        Schema::create('one_to_one_program_starting_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('one_to_one_program_id')->nullable();
            $table->foreign('one_to_one_program_id')->references('id')->on('one_to_one_programs')->onDelete('cascade');
            $table->date('starting_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_program_starting_dates');
    }
};
