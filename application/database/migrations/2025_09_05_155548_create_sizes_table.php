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
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('type')->nullable();
            $table->integer('number')->nullable();
            $table->string('size')->nullable();
            $table->integer('size_1')->nullable();
            $table->integer('size_2')->nullable();
            $table->integer('size_3')->nullable();
            $table->string('size_blank')->nullable();
            $table->integer('count_1')->nullable();
            $table->integer('count_2')->nullable();
            $table->string('count_blank')->nullable();
            $table->string('size_paper')->nullable();

            $table->string('package')->nullable();
            $table->string('membrane')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
