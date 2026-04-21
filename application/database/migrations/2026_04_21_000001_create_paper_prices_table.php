<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_prices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('match_key')->unique();
            $table->string('title');
            $table->string('group_name')->nullable();
            $table->string('sheet_format')->nullable();
            $table->decimal('base_price', 12, 3)->nullable();
            $table->decimal('markup_percent', 6, 2)->default(10);
            $table->decimal('sale_price', 12, 3)->nullable();
            $table->text('note')->nullable();
        });

        DB::table('paper_prices')->insert([
            [
                'match_key' => 'bumaga-mel-170g-6294',
                'title' => 'Мел 170г 940*620',
                'group_name' => 'Меловка',
                'sheet_format' => '940*620',
                'base_price' => 12.05,
                'markup_percent' => 10,
                'sale_price' => 13.255,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-170g-72104',
                'title' => 'Мел 170г 1040*720',
                'group_name' => 'Меловка',
                'sheet_format' => '1040*720',
                'base_price' => 15.50,
                'markup_percent' => 10,
                'sale_price' => 17.05,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-200g-6294',
                'title' => 'Мел 200г 940*620',
                'group_name' => 'Меловка 200 62',
                'sheet_format' => '940*620',
                'base_price' => 13.85,
                'markup_percent' => 10,
                'sale_price' => 15.235,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-200g-72104',
                'title' => 'Мел 200г 1040*720',
                'group_name' => 'Меловка 200 72',
                'sheet_format' => '1040*720',
                'base_price' => 17.75,
                'markup_percent' => 10,
                'sale_price' => 19.525,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-250g-6294',
                'title' => 'Мел 250г 940*620',
                'group_name' => 'Меловка 250 62',
                'sheet_format' => '940*620',
                'base_price' => 20.98,
                'markup_percent' => 10,
                'sale_price' => 23.078,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-250g-72104',
                'title' => 'Мел 250г 1040*720',
                'group_name' => 'Меловка 250 72',
                'sheet_format' => '1040*720',
                'base_price' => 26.70,
                'markup_percent' => 10,
                'sale_price' => 29.37,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-300g-6294',
                'title' => 'мел 300 940х620',
                'group_name' => 'Меловка',
                'sheet_format' => '940*620',
                'base_price' => 24.35,
                'markup_percent' => 10,
                'sale_price' => 26.785,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-mel-300g-72104',
                'title' => 'мел 300 1040х720',
                'group_name' => 'Меловка',
                'sheet_format' => '1040*720',
                'base_price' => 26.31,
                'markup_percent' => 10,
                'sale_price' => 28.941,
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-karton-235g-6294',
                'title' => 'картон кама, 235гр, белый, 940*620',
                'group_name' => 'кама',
                'sheet_format' => '940*620',
                'base_price' => 16.82,
                'markup_percent' => 10,
                'sale_price' => 18.502,
                'note' => 'дубль в',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-karton-235g-72104',
                'title' => 'картон кама, 235гр, белый, 1040*720',
                'group_name' => 'кама',
                'sheet_format' => '1040*720',
                'base_price' => 21.55,
                'markup_percent' => 10,
                'sale_price' => 23.705,
                'note' => 'папирус дубль в',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-karton-250g-6294',
                'title' => 'картон  250, 940*620',
                'group_name' => 'картон 250 62',
                'sheet_format' => '940*620',
                'base_price' => 18.10,
                'markup_percent' => 10,
                'sale_price' => 19.91,
                'note' => 'папирус аверс',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'match_key' => 'bumaga-karton-250g-72104',
                'title' => 'картон  250, 1040*720',
                'group_name' => 'картон 250 72',
                'sheet_format' => '1040*720',
                'base_price' => 23.26,
                'markup_percent' => 10,
                'sale_price' => 25.586,
                'note' => 'папирус аверс',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_prices');
    }
};
