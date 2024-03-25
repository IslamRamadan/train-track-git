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
        Schema::create('one_to_one_programs', function (Blueprint $table) {
            $table->id();
//            $table->index('client_id');
//            $table->foreign('client_id')->references('id')->on('users')->onDelete('strict');
//            $table->index('coach_id');
//            $table->foreign('coach_id')->references('id')->on('users')->onDelete('strict');
//            $table->index('program_id');
//            $table->foreign('program_id')->references('id')->on('programs')->onDelete('strict');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('users');
            $table->unsignedBigInteger('coach_id');
            $table->foreign('coach_id')->references('id')->on('users');
            $table->unsignedBigInteger('program_id ');
            $table->foreign('program_id')->references('id')->on('programs');

            $table->string('name');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_to_one_programs');
    }
};
