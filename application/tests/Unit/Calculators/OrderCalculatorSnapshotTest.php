<?php

namespace Tests\Unit\Calculators;

use App\Domain\Orders\OrderCalculationInput;
use App\Domain\Orders\OrderCalculator;
use App\Models\Category;
use App\Models\Ofset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderCalculatorSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('sizes')->insert([
            'id' => 1,
            'type' => 'A2',
            'number' => 101,
            'size' => 'test-size',
            'size_1' => 200,
            'size_2' => 300,
            'size_3' => 100,
            'size_blank' => 'blank',
            'count_1' => 4,
            'count_2' => 2,
            'count_blank' => '2',
            'size_paper' => '720*1040',
            'package' => '200',
            'membrane' => '620',
            'length_membrane' => 0.62,
            'length_cord' => 0.8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('groups')->insert([
            'number_id' => 101,
            'group_name' => 'A3,A2',
            'bottom' => 1.0,
            'handle_1' => 1.5,
            'handle_2' => 2.0,
            'luvers' => 0.7,
            'cutting_cord_2' => 0.4,
            'sidewall' => 0.6,
            'boking_gluing' => 0.8,
            'hole' => 0.2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Category::query()->create(['id' => 1, 'name' => 'Мелованная 200г', 'slug' => 'melovannaia-200g']);
        Category::query()->create(['id' => 2, 'name' => 'Картон 250г', 'slug' => 'karton-250g']);
        Category::query()->create(['id' => 3, 'name' => 'Матовая', 'slug' => 'matovaia']);
        Category::query()->create(['id' => 4, 'name' => 'Шнур', 'slug' => 'snur-vitoi']);
        Category::query()->create(['id' => 5, 'name' => 'Лента', 'slug' => 'lenta']);

        DB::table('products')->insert([
            ['id' => 1, 'name' => 'Удар вырубки', 'slug' => 'felling-blow', 'price' => 0.50, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Приладка вырубки', 'slug' => 'felling-prebuild', 'price' => 100.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Пластина печати', 'slug' => 'print-plate', 'price' => 450.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Работа ламинации', 'slug' => 'lamination-work', 'price' => 1.20, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Приладка ламинации', 'slug' => 'lamination-prebuild', 'price' => 200.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 32, 'name' => 'Коробка', 'slug' => 'box', 'price' => 30.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Бумага мел', 'slug' => 'bumaga-mel-200g-72104', 'price' => 25.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Бумага картон', 'slug' => 'bumaga-karton-250g-72104', 'price' => 35.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ламинация матовая', 'slug' => 'laminaciia-matovaia', 'price' => 12.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Шнур витой', 'slug' => 'snur-vitoi', 'price' => 4.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Лента', 'slug' => 'lenta', 'price' => 3.00, 'is_visible' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Ofset::query()->create([
            'colors' => '2+0',
            'circulation_100' => 500,
            'circulation_300' => 900,
            'circulation_500' => 1200,
            'circulation_1000' => 1800,
            'circulation_2000' => 3000,
            'circulation_3000' => 4200,
            'circulation_5000' => 7000,
            'circulation_10000' => 12000,
            'circulation_20000' => 22000,
            'circulation_50000' => 50000,
            'circulation_100000' => 95000,
            'circulation_500000' => 420000,
            'circulation_1000000' => 800000,
        ]);

        Ofset::query()->create([
            'colors' => '1+1',
            'circulation_100' => 650,
            'circulation_300' => 1000,
            'circulation_500' => 1500,
            'circulation_1000' => 2200,
            'circulation_2000' => 3600,
            'circulation_3000' => 5000,
            'circulation_5000' => 8000,
            'circulation_10000' => 13500,
            'circulation_20000' => 24000,
            'circulation_50000' => 56000,
            'circulation_100000' => 105000,
            'circulation_500000' => 470000,
            'circulation_1000000' => 900000,
        ]);

        DB::table('price_lacs')->insert([
            'process_type' => 'Сплошное',
            'lacquer_type' => 'Матовая',
            'format' => 'A2',
            'min_run' => 0,
            'max_run' => 100000,
            'price' => 2.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function scenarios(): array
    {
        return [
            'base_direct' => [[
                'size_id' => 1,
                'paper_circulation' => 1000,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => [],
                'print_type' => [],
            ]],
            'base_agency' => [[
                'size_id' => 1,
                'paper_circulation' => 1000,
                'type_paper' => 1,
                'type_order' => 'agency',
                'print_options' => [],
                'print_type' => [],
            ]],
            'offset_only' => [[
                'size_id' => 1,
                'paper_circulation' => 2500,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['print'],
                'print_type' => ['ofset'],
                'print_type_ofset' => '2+0',
                'print_type_ofset_2' => '1+1',
            ]],
            'offset_with_plashka' => [[
                'size_id' => 1,
                'paper_circulation' => 2500,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['print'],
                'print_type' => ['ofset'],
                'print_type_ofset' => '2+0',
                'print_plashka' => true,
            ]],
            'lamination_only' => [[
                'size_id' => 1,
                'paper_circulation' => 5000,
                'type_paper' => 2,
                'type_order' => 'direct',
                'print_options' => ['lamination'],
                'print_type' => [],
                'type_lamination' => 'matovaia',
            ]],
            'post_print_uf_lak' => [[
                'size_id' => 1,
                'paper_circulation' => 3000,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['post_print'],
                'print_type' => [],
                'post_print_type' => ['uf-lak'],
            ]],
            'post_print_uf_lak_discharge' => [[
                'size_id' => 1,
                'paper_circulation' => 3000,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['post_print'],
                'print_type' => [],
                'post_print_type' => ['uf-lak'],
                'print_option_discharge' => true,
            ]],
            'handle_and_luvers' => [[
                'size_id' => 1,
                'paper_circulation' => 1200,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['handle', 'luvers'],
                'print_type' => [],
                'type_handle' => 4,
                'type_bracing_handle' => 'vkleika',
            ]],
            'handle_x2' => [[
                'size_id' => 1,
                'paper_circulation' => 1200,
                'type_paper' => 1,
                'type_order' => 'direct',
                'print_options' => ['handle'],
                'print_type' => [],
                'type_handle' => 5,
                'handle_x2' => true,
            ]],
            'complex_combo' => [[
                'size_id' => 1,
                'paper_circulation' => 6000,
                'type_paper' => 2,
                'type_order' => 'agency',
                'print_options' => ['print', 'lamination', 'post_print', 'handle', 'luvers'],
                'print_type' => ['ofset', 'selkografiia'],
                'print_type_ofset' => '2+0',
                'print_type_ofset_2' => '1+1',
                'print_type_selkografiia' => '1+0',
                'print_plashka' => true,
                'type_lamination' => 'matovaia',
                'post_print_type' => ['uf-lak'],
                'print_option_discharge' => true,
                'type_handle' => 4,
                'type_bracing_handle' => 'uzel',
            ]],
        ];
    }

    /**
     * @dataProvider scenarios
     */
    public function test_calculation_snapshot(array $payload): void
    {
        $calculator = app(OrderCalculator::class);

        $result = $calculator->calculate(OrderCalculationInput::fromArray($payload));

        $snapshotData = [
            'metrics' => $result->metrics,
            'components' => $result->components,
        ];

        $name = 'order_calculator_'.preg_replace('/[^a-z0-9_]+/i', '_', (string) $this->name());
        $this->assertMatchesSnapshot($name, $snapshotData);
    }

    private function assertMatchesSnapshot(string $name, array $data): void
    {
        $snapshotDir = base_path('tests/Unit/Calculators/__snapshots__');
        $snapshotPath = $snapshotDir.'/'.$name.'.json';

        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0777, true);
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!file_exists($snapshotPath)) {
            file_put_contents($snapshotPath, $encoded.PHP_EOL);
            $this->markTestIncomplete("Snapshot created: {$snapshotPath}. Run tests again.");
        }

        $this->assertSame(trim((string) file_get_contents($snapshotPath)), trim((string) $encoded));
    }
}
