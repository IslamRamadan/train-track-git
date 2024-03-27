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
        Schema::create('oto_exercise_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->date('date');
            $table->enum('sender', ['0', '1']);
            $table->unsignedBigInteger('oto_program_id');
            $table->foreign('oto_program_id')->references('id')->on('one_to_one_programs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oto_exercise_comments');
    }
};
