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
        Schema::create('one_to_one_program_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('arrangement');
            $table->unsignedBigInteger('one_to_one_program_id');
            $table->foreign('one_to_one_program_id')->references('id')->on('one_to_one_programs');
            $table->date("date");
            $table->enum('is_done', ["0", "1"])->default("0");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_program_exercises');
    }
};
