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
        Schema::create('price_lacs', function (Blueprint $table) {

            $table->id();
            $table->string('process_type');
            $table->string('lacquer_type');//, ['выборочное', 'сплошное']);
            $table->enum('format', ['A3', 'A2', 'A1']);
            $table->integer('min_run');
            $table->integer('max_run')->nullable();
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_lacs');
    }
};
