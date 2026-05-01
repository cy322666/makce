<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->string('paper_format', 1)->nullable()->after('size_paper');
        });

        // Формат листа нужен отдельно от текущего `type`, потому что `type`
        // уже участвует в расчетах как A1/A2/A3 и его трогать нельзя.
        DB::table('sizes')
            ->whereRaw("regexp_replace(lower(coalesce(size_paper, '')), '[[:space:]]+', '', 'g') ~ ?", ['^(620[\\*x]940|940[\\*x]620)$'])
            ->update(['paper_format' => 'A']);

        DB::table('sizes')
            ->whereRaw("regexp_replace(lower(coalesce(size_paper, '')), '[[:space:]]+', '', 'g') ~ ?", ['^(720[\\*x]1040|1040[\\*x]720|700[\\*x]1000|1000[\\*x]700)$'])
            ->update(['paper_format' => 'B']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->dropColumn('paper_format');
        });
    }
};
