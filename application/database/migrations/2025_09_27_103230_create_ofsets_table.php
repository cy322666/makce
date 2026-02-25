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
        Schema::create('ofsets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('colors')->nullable();
            $table->string('sale_preparation')->nullable();
            $table->string('sale_print')->nullable();
            $table->string('sale_print_mel_paper')->nullable();
            $table->string('circulation_100')->nullable();
            $table->string('circulation_300')->nullable();
            $table->string('circulation_500')->nullable();
            $table->string('circulation_1000')->nullable();
            $table->string('circulation_2000')->nullable();
            $table->string('circulation_3000')->nullable();
            $table->string('circulation_5000')->nullable();
            $table->string('circulation_10000')->nullable();
            $table->string('circulation_15000')->nullable();
            $table->string('circulation_20000')->nullable();
            $table->string('circulation_50000')->nullable();
            $table->string('circulation_100000')->nullable();
            $table->string('circulation_500000')->nullable();
            $table->string('circulation_1000000')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofsets');
    }
};
