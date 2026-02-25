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
        Schema::create('uv_lac_prices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('format', ['A3', 'A2', 'A1']);
            $table->decimal('min_total_price', 10, 2);
            $table->integer('coverage_from');
            $table->integer('coverage_to')->nullable(); // null = "более 80%"
            $table->decimal('price_per_sheet', 10, 2)->nullable(); // null если нет цен
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uv_lac_prices');
    }
};
