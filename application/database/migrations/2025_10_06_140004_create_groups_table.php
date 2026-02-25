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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('number_id')->nullable();

            $table->string( 'group_name')->nullable();

            $table->decimal( 'bottom')->nullable();
            $table->decimal( 'handle_1')->nullable();
            $table->decimal( 'handle_2',)->nullable();
            $table->decimal( 'luvers',)->nullable();
            $table->decimal( 'cutting_cord_2')->nullable();
            $table->decimal( 'sidewall',)->nullable();
            $table->decimal( 'boking_gluing')->nullable();
            $table->decimal( 'hole')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
