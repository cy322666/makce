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
        Schema::create('print_prices', function (Blueprint $table) {
            $table->id();
            $table->enum('format', ['A3', 'A2', 'A1']);
            $table->decimal('min_total_price', 10, 2);
            $table->integer('coverage_from');
            $table->integer('coverage_to')->nullable(); // null — если "более 80%"
            $table->decimal('price_per_sheet', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_prices');
    }
};
