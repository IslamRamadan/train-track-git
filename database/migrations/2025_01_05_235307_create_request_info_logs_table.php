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
        Schema::create('request_info_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id")->nullable();
            $table->string("ip", 191)->nullable();
            $table->string("user_agent", 191)->nullable();
            $table->string("route", 191)->nullable();
            $table->text("body")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_info_logs');
    }
};
