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
        Schema::create('gym_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gym_id');
            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->string('order_id', 191)->unique();
            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('packages');
            $table->float('amount');
            $table->enum('status', ['1', '2', '3'])->default('1'); // 1=Unpaid, 2=Paid, 3=Cancelled
            $table->enum('upgrade', ['0', '1'])->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_payments');
    }
};
