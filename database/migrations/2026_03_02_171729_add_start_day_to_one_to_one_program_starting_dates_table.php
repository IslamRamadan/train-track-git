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
        Schema::table('one_to_one_program_starting_dates', function (Blueprint $table) {
            $table->unsignedInteger('start_day')->default(1)->after('starting_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_to_one_program_starting_dates', function (Blueprint $table) {
            $table->dropColumn('start_day');
        });
    }
};
