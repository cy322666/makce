<?php

namespace App\Domain\Orders;

use App\Models\Category;
use App\Models\Group;
use App\Models\LacPrice;
use App\Models\Ofset;
use App\Models\Product;
use App\Models\Size;

class OrderCalculator
{
    public function calculate(OrderCalculationInput $input): OrderCalculationResult
    {
        $size = $input->sizeId ? Size::query()->find($input->sizeId) : null;
        $group = $size ? Group::query()->where('number_id', $size->number)->first() : null;

        $sizeFormat = $this->resolveFormatPrint($size);
        $sale1Channel = $size?->channel ? (float) $size->channel : 0.0;
        $sale1Page = $this->resolveSale1Paper($input, $size);

        $printPrebuild = $this->calculatePrintPrebuild($input);
        $paperPackWork = $this->calculatePaperPackWork($input, $printPrebuild);
        $countPaper = $this->calculateCountPaper($input, $size);
        $printPagesCirculation = $this->calculatePrintPagesCirculation($input, $size);

        $paperResult = $this->calculatePaperResult($paperPackWork, $sale1Page, $size);

        $printSaleResult = $this->calculatePrintSale($input);
        $postPrintSale = $this->calculatePostPrintSale($input, $printPagesCirculation, $sizeFormat);

        $laminationData = $this->calculateLamination($input, $size, $sizeFormat, $printPagesCirculation);
        $fellingData = $this->calculateFelling($input, $size, $printPagesCirculation, $sale1Channel);
        $assemblyResult = $this->calculateCutting($input);
        $assemblyResultCirculation = $this->calculateAssemblyCirculation($input, $size, $group);
        $handleData = $this->calculateHandle($input, $size, $group);
        $packagesResult = $this->calculatePackages($input, $size);

        $components = [
            'paper' => [
                'materials' => $paperResult,
                'works' => 0.0,
            ],
            'print' => [
                'materials' => 0.0,
                'works' => $printSaleResult,
            ],
            'post_print' => [
                'materials' => 0.0,
                'works' => $postPrintSale,
            ],
            'lamination' => [
                'materials' => $laminationData['materials'],
                'works' => $laminationData['works'],
            ],
            'felling' => [
                'materials' => $fellingData['materials'],
                'works' => $fellingData['works'],
            ],
            'handle' => [
                'materials' => $handleData['materials'],
                'works' => $handleData['works'],
            ],
            'assembly' => [
                'materials' => 0.0,
                'works' => $assemblyResult + $assemblyResultCirculation,
            ],
            'packaging' => [
                'materials' => $packagesResult,
                'works' => 0.0,
            ],
        ];

        foreach ($components as $key => $component) {
            $components[$key]['total'] = $component['materials'] + $component['works'];
        }

        $resultMaterials = array_sum(array_column($components, 'materials'));
        $resultWorks = array_sum(array_column($components, 'works'));
        $subtotal = $resultMaterials + $resultWorks;

        $markupPercent = $this->resolveMarkupPercent($input->typeOrder);
        $resultCirculation = $subtotal * (1 + ($markupPercent / 100));

        $metrics = [
            'group_name' => $size?->getGroupName() ?? '',
            'size_name' => $size?->number,
            'size_format' => $sizeFormat,
            'group_info' => $this->groupInfoText($group),

            'markup' => $markupPercent,
            'sale_1_page' => $sale1Page,
            'sale_1_channel' => $sale1Channel,

            'paper_pack_work' => round($paperPackWork),
            'count_paper' => round($countPaper),
            'print_pages_circulation' => round($printPagesCirculation),
            'print_prebuild' => $printPrebuild,

            'paper_result' => round($paperResult, 2),
            'print_sale_result' => round($printSaleResult, 2),
            'post_print_sale' => round($postPrintSale, 2),

            'lamination_result' => round($laminationData['total'], 2),
            'prebuild_count' => $laminationData['prebuild_count'],

            'felling_result' => round($fellingData['total'], 2),
            'assembly_result' => round($assemblyResult, 2),
            'assembly_result_circulation' => round($assemblyResultCirculation, 2),
            'handle_result' => round($handleData['total'], 2),
            'packages_result' => round($packagesResult, 2),

            'result_materials' => round($resultMaterials, 2),
            'result_works' => round($resultWorks, 2),
            'result_circulation' => round($resultCirculation, 2),
        ];

        return new OrderCalculationResult($metrics, $components);
    }

    private function resolveMarkupPercent(?string $typeOrder): float
    {
        return (float) config("calculator.markup_percent.{$typeOrder}", 0);
    }

    private function calculatePaperPackWork(OrderCalculationInput $input, int $printPrebuild): float
    {
        if ($input->paperCirculation <= 0) {
            return 0;
        }

        $wastePercent = (float) config('calculator.paper.waste_percent', 108);

        return (($input->paperCirculation / 100) * $wastePercent) + ($printPrebuild / 2);
    }

    private function calculateCountPaper(OrderCalculationInput $input, ?Size $size): float
    {
        if (!$size || $input->paperCirculation <= 0) {
            return 0;
        }

        $countToPage = (float) (config("calculator.count_per_a2_sheet.{$size->type}") ?? 0);

        if ($countToPage <= 0) {
            return 0;
        }

        $wastePercent = (float) config('calculator.paper.waste_percent', 108);

        return ($input->paperCirculation / 100) * $wastePercent / $countToPage;
    }

    private function calculatePaperResult(float $paperPackWork, float $sale1Page, ?Size $size): float
    {
        if (!$size || !$sale1Page || !$size->count_1) {
            return 0;
        }

        return ($paperPackWork / ((float) $size->count_1 / 2)) * $sale1Page;
    }

    private function calculatePrintPagesCirculation(OrderCalculationInput $input, ?Size $size): float
    {
        if (!$size || $input->paperCirculation <= 0) {
            return 0;
        }

        return match ($size->type) {
            'A1' => (float) $input->paperCirculation,
            'A2' => $this->safeDivide($input->paperCirculation, ((float) $size->count_blank / 2)),
            'A3' => $this->safeDivide($input->paperCirculation, ((float) $size->count_blank / 2 * 2)),
            'A4' => $this->safeDivide($input->paperCirculation, ((float) $size->count_blank / 2 * 4)),
            'A5' => $this->safeDivide($input->paperCirculation, ((float) $size->count_blank / 2 * 8)),
            'A6' => $this->safeDivide($input->paperCirculation, ((float) $size->count_blank / 2 * 16)),
            default => 0,
        };
    }

    private function resolveFormatPrint(?Size $size): string
    {
        if (!$size?->type) {
            return '';
        }

        return $size->type !== 'A1' ? 'A2' : 'A1';
    }

    private function resolveSale1Paper(OrderCalculationInput $input, ?Size $size): float
    {
        if (!$size || !$input->typePaperId) {
            return 0;
        }

        $category = Category::query()->find($input->typePaperId);

        if (!$category) {
            return 0;
        }

        $slugTypePaper = (string) $category->slug;
        $paperPrefix = '';

        foreach ((array) config('calculator.paper_slug_map', []) as $contains => $prefix) {
            if (str_contains($slugTypePaper, (string) $contains)) {
                $paperPrefix = (string) $prefix;
                break;
            }
        }

        if ($paperPrefix === '') {
            return 0;
        }

        $parts = explode('-', $slugTypePaper);
        $weight = $parts[1] ?? '';

        if ($weight === '') {
            return 0;
        }

        $searchSlug = $paperPrefix.'-'.$weight;

        $suffix = config("calculator.size_paper_suffix_map.{$size->size_paper}");
        if ($suffix) {
            $searchSlug .= '-'.$suffix;
        }

        return (float) (Product::query()->where('slug', $searchSlug)->first()?->price ?? 0);
    }

    private function calculatePrintSale(OrderCalculationInput $input): float
    {
        if (!$input->printTypeOfset || $input->paperCirculation <= 0) {
            return 0;
        }

        $ofset = Ofset::query()->where('colors', $input->printTypeOfset)->first();
        if (!$ofset) {
            return 0;
        }

        $sizes = [100, 300, 500, 1000, 2000, 3000, 5000, 10000, 20000, 50000, 100000, 500000, 1000000];

        foreach ($sizes as $size) {
            if ($input->paperCirculation <= $size) {
                $property = 'circulation_'.$size;

                return (float) ($ofset->{$property} ?? 0);
            }
        }

        return 0;
    }

    private function calculatePostPrintSale(OrderCalculationInput $input, float $printPagesCirculation, string $sizeFormat): float
    {
        if (!$this->contains($input->printOptions, 'post_print')) {
            return 0;
        }

        if (!$this->contains($input->postPrintType, 'uf-lak')) {
            return 0;
        }

        return $this->calculateUfLak($input, $printPagesCirculation, $sizeFormat);
    }

    private function calculateUfLak(OrderCalculationInput $input, float $printPagesCirculation, string $sizeFormat): float
    {
        if ($printPagesCirculation <= 0 || $sizeFormat === '') {
            return 0;
        }

        $preSale = (float) (LacPrice::query()
            ->where('min_run', '<=', $printPagesCirculation)
            ->where('max_run', '>=', $printPagesCirculation)
            ->where('lacquer_type', 'Матовая')
            ->where('process_type', 'Сплошное')
            ->where('format', $sizeFormat)
            ->first()?->price ?? 0) * $printPagesCirculation;

        $dryingPerSheet = (float) config("calculator.uv_lac.drying_per_sheet.{$sizeFormat}", 0);
        $sale = ($dryingPerSheet * $printPagesCirculation) + $preSale;

        if ($input->printOptionDischarge) {
            $dischargePerSheet = (float) config("calculator.uv_lac.discharge_per_sheet.{$sizeFormat}", 0);
            $sale += $dischargePerSheet * $printPagesCirculation;
        }

        return $sale;
    }

    private function calculateLamination(OrderCalculationInput $input, ?Size $size, string $sizeFormat, float $printPagesCirculation): array
    {
        if (!$input->typeLamination || !$size || $printPagesCirculation <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'prebuild_count' => 0,
            ];
        }

        $categoryType = Category::query()->where('slug', $input->typeLamination)->first();
        if (!$categoryType) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'prebuild_count' => 0,
            ];
        }

        $sale = (float) (Product::query()
            ->where('slug', 'laminaciia-'.$categoryType->slug)
            ->first()?->price ?? 0);

        $membraneMaterials = $this->calculateMembraneCost($size, $sale);

        $price1 = (float) (Product::query()->find(config('calculator.products.lamination_prebuild'))?->price ?? 0);
        $sale1Prebuild = $sizeFormat === 'A1' ? $price1 : $price1 * 2;
        $countPrebuild = $this->countSteps($printPagesCirculation, (int) config('calculator.prebuild.lamination_step', 2000), true);

        $prebuildWork = $countPrebuild * $sale1Prebuild;

        $price1work = (float) (Product::query()->find(config('calculator.products.lamination_work'))?->price ?? 0);
        $work = $printPagesCirculation * $price1work;

        $works = $prebuildWork + $work;
        $total = $membraneMaterials + $works;

        return [
            'materials' => $membraneMaterials,
            'works' => $works,
            'total' => $total,
            'prebuild_count' => $countPrebuild,
        ];
    }

    private function calculateMembraneCost(Size $size, float $sale): float
    {
        if (!$size->membrane || !$size->length_membrane) {
            return 0;
        }

        $m2 = ((float) $size->membrane / 1000) * (float) $size->length_membrane;

        return $m2 * $sale;
    }

    private function calculateFelling(OrderCalculationInput $input, ?Size $size, float $printPagesCirculation, float $sale1Channel): array
    {
        if (!$size || $input->paperCirculation <= 0 || $printPagesCirculation <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
            ];
        }

        $prebuild1Sale = (float) (Product::query()->find(config('calculator.products.felling_prebuild'))?->price ?? 0);
        $sale1Blow = (float) (Product::query()->find(config('calculator.products.felling_blow'))?->price ?? 0);

        $countChannel = $size->channel ? (float) $size->channel : 1.0;
        $countBlows = $printPagesCirculation * (float) $size->count_blank;

        $channelMaterials = $countBlows * ($countChannel + $sale1Channel);
        $blowWorks = $countBlows * $sale1Blow;

        $prebuildCount = $this->countSteps($printPagesCirculation, (int) config('calculator.prebuild.felling_step', 2000), false);
        $prebuildWorks = $prebuildCount * $prebuild1Sale;

        $works = $blowWorks + $prebuildWorks;
        $total = $channelMaterials + $works;

        return [
            'materials' => $channelMaterials,
            'works' => $works,
            'total' => $total,
        ];
    }

    private function calculateCutting(OrderCalculationInput $input): float
    {
        if ($input->paperCirculation <= 0) {
            return 0;
        }

        $minimum = (float) config('calculator.cutting.minimum_price', 200);
        $stepQuantity = (int) config('calculator.cutting.step_quantity', 1000);
        $stepPrice = (float) config('calculator.cutting.step_price', 200);

        if ($input->paperCirculation < $stepQuantity) {
            return $minimum;
        }

        return ceil($input->paperCirculation / $stepQuantity) * $stepPrice;
    }

    private function calculateAssemblyCirculation(OrderCalculationInput $input, ?Size $size, ?Group $group): float
    {
        if (!$size || !$group || $input->paperCirculation <= 0) {
            return 0;
        }

        $sleeve = ((float) $size->count_blank === 1.0)
            ? (float) ($group->handle_1 ?? 0)
            : (float) ($group->handle_2 ?? 0);

        $basePerUnit =
            (float) ($group->bottom ?? 0) +
            (float) ($group->sidewall ?? 0) +
            (float) ($group->boking_gluing ?? 0) +
            (float) ($group->cutting_cord_2 ?? 0) +
            $sleeve;

        $glueExtra = (float) (config("calculator.glue_per_format.{$size->type}") ?? 0);

        return ($basePerUnit + $glueExtra) * $input->paperCirculation;
    }

    private function calculateHandle(OrderCalculationInput $input, ?Size $size, ?Group $group): array
    {
        if (!$input->typeHandleId || !$size || !$group || $input->paperCirculation <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
            ];
        }

        $handle = ((float) $size->count_blank === 1.0)
            ? (float) ($group->handle_1 ?? 0)
            : (float) ($group->handle_2 ?? 0);

        $workPerUnit = (float) ($group->sidewall ?? 0) + (float) ($group->boking_gluing ?? 0) + $handle;

        $category = Category::query()->find($input->typeHandleId);
        $materialSale = (float) (Product::query()->where('slug', $category?->slug)->first()?->price ?? 0);

        if ($category && str_contains((string) $category->slug, 'snur')) {
            $workPerUnit += (float) ($group->hole ?? 0);
        }

        if ($this->contains($input->printOptions, 'luvers')) {
            $workPerUnit += (float) ($group->luvers ?? 0);
        }

        if ($input->typeBracingHandle) {
            $workPerUnit += (float) config("calculator.bracing_handle_prices.{$input->typeBracingHandle}", 0);
        }

        if ($input->handleX2) {
            $workPerUnit *= 2;
            $materialSale *= 2;
        }

        $materials = ((float) $size->length_cord * $materialSale) * $input->paperCirculation;
        $works = $workPerUnit * $input->paperCirculation;

        return [
            'materials' => $materials,
            'works' => $works,
            'total' => $materials + $works,
        ];
    }

    private function calculatePackages(OrderCalculationInput $input, ?Size $size): float
    {
        if (!$size || $input->paperCirculation <= 0 || !$size->package) {
            return 0;
        }

        $countBox = round($input->paperCirculation / (float) $size->package);
        $sale1Box = (float) (Product::query()->find(config('calculator.products.package_box'))?->price ?? 0);

        return $countBox * $sale1Box;
    }

    private function calculatePrintPrebuild(OrderCalculationInput $input): int
    {
        $offsetSheets = 0;
        $silkSheets = 0;

        if ($this->contains($input->printType, 'ofset')) {
            $offsetColorCount = $this->parseColorPair($input->printTypeOfset) + $this->parseColorPair($input->printTypeOfset2);
            $offsetSheets = $offsetColorCount * (int) config('calculator.prebuild.offset_per_color_sheets', 50);
        }

        if ($this->contains($input->printType, 'selkografiia')) {
            $silkSheets = $this->parseColorPair($input->printTypeSelkografiia) * (int) config('calculator.prebuild.silk_per_color_sheets', 10);
        }

        $plashkaSheets = $input->printPlashka ? (int) config('calculator.prebuild.plashka_sheets', 50) : 0;

        return $offsetSheets + $silkSheets + $plashkaSheets;
    }

    private function parseColorPair(?string $value): int
    {
        if (!$value) {
            return 0;
        }

        if (str_contains($value, '+')) {
            $parts = explode('+', $value);

            return array_sum(array_map(static fn ($part) => (int) $part, $parts));
        }

        return (int) $value;
    }

    private function countSteps(float $quantity, int $step, bool $atLeastOne): int
    {
        if ($quantity <= 0 || $step <= 0) {
            return 0;
        }

        $steps = (int) ceil($quantity / $step);

        if ($atLeastOne) {
            return max(1, $steps);
        }

        return max(0, $steps);
    }

    private function contains(array $items, string $needle): bool
    {
        return in_array($needle, $items, true);
    }

    private function safeDivide(float|int $value, float|int $by): float
    {
        if ((float) $by === 0.0) {
            return 0;
        }

        return (float) $value / (float) $by;
    }

    private function groupInfoText(?Group $group): string
    {
        if (!$group) {
            return 'Размер или группа не указаны';
        }

        return implode("\n", [
            'Дно : '.($group->bottom ?? 0),
            'Рукав 1 : '.($group->handle_1 ?? 0),
            'Рукав 2 : '.($group->handle_2 ?? 0),
            'Люверс : '.($group->luvers ?? 0),
            'Резка шнура 2шт : '.($group->cutting_cord_2 ?? 0),
            'Вставка боковины : '.($group->sidewall ?? 0),
            'Вклейка боковины : '.($group->boking_gluing ?? 0),
            'Дырка : '.($group->hole ?? 0),
        ]);
    }
}
