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
        Schema::create('users_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->foreign('coach_id')->references('id')->on('users');
            $table->enum("status", ["0", "1", "2"])->default("1");
            $table->string("order_id", 191);
            $table->float("amount");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_payments');
    }
};
