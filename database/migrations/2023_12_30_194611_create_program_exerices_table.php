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
        Schema::create('program_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('day');
            $table->integer('arrangement');
            // Create the program_id column first
            $table->unsignedBigInteger('program_id');
            // Then add the foreign key constraint
            $table->foreign('program_id')->references('id')->on('programs');
            // Remove this line - it's incorrect:
            // $table->unsignedBigInteger('program_exercises_program_id_foreign')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_exerices');
    }
};
