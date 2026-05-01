<?php

namespace App\Domain\BagCalculatorV2;

use App\Models\Category;
use App\Models\Group;
use App\Models\LacPrice;
use App\Models\Ofset;
use App\Models\PaperPrice;
use App\Models\Product;
use App\Models\Size;

final class BagCalculator
{
    /**
     * Производственная v2-логика:
     * - бумага и печать считаются отдельно;
     * - для приладки используем правило из документа клиента;
     * - этапы держим раздельно, чтобы было видно, откуда растет сумма.
     */
    public function calculate(BagCalculationInput $input): BagCalculationResult
    {
        $size = $input->sizeId ? Size::query()->find($input->sizeId) : null;
        $group = $size ? Group::query()->where('number_id', $size->number)->first() : null;
        $paper = $input->paperPriceId ? PaperPrice::query()->find($input->paperPriceId) : null;
        $offset = $input->offsetPriceId ? Ofset::query()->find($input->offsetPriceId) : null;
        $handleCategory = $input->typeHandleId ? Category::query()->find($input->typeHandleId) : null;
        $bracingCategory = $input->typeBracingHandle ? Category::query()->where('slug', $input->typeBracingHandle)->first() : null;

        $sizeFormat = $this->resolveFormatPrint($size);
        $paperFormat = $this->resolvePaperFormat($size);
        $paperPrice = $this->resolvePaperPrice($paper);
        $printPrebuildSheets = $this->calculatePrintPrebuildSheets($input);
        $paperSheets = $this->calculatePaperSheets($input, $size, $printPrebuildSheets);
        $paperSheetsInWork = $paperSheets;
        $paperTotal = $paperSheetsInWork * $paperPrice;

        $offsetData = $this->calculateOffsetPrint($input, $offset, $paper, $size);
        $silkData = $this->calculateSilkPrint($input, $paperSheets);
        $uvLacData = $this->calculateUvLac($input, $size, $sizeFormat, $paperSheets);
        $laminationData = $this->calculateLamination($input, $size, $paperSheets);
        $dieCuttingData = $this->calculateDieCutting($input, $size, $paperSheets);
        $cuttingTotal = $this->calculateCutting($input);
        $handleData = $this->calculateHandle($input, $size, $group, $handleCategory, $bracingCategory);
        $assemblyData = $this->calculateAssembly($input, $size, $group);
        $bottomData = $this->calculateBottom($input, $size, $group);
        $packagingTotal = $this->calculatePackaging($input, $size);
        $luversTotal = $this->calculateLuvers($input, $group);

        $components = [
            'paper' => [
                'materials' => $paperTotal,
                'works' => 0.0,
            ],
            'offset_print' => [
                'materials' => 0.0,
                'works' => $offsetData['total'],
            ],
            'silk_print' => [
                'materials' => 0.0,
                'works' => $silkData['total'],
            ],
            'uv_lac' => [
                'materials' => 0.0,
                'works' => $uvLacData['total'],
            ],
            'lamination' => [
                'materials' => $laminationData['materials'],
                'works' => $laminationData['works'],
            ],
            'die_cutting' => [
                'materials' => 0.0,
                'works' => $dieCuttingData['total'],
            ],
            'cutting' => [
                'materials' => 0.0,
                'works' => $cuttingTotal,
            ],
            'handle' => [
                'materials' => $handleData['materials'],
                'works' => $handleData['works'],
            ],
            'assembly_gluing' => [
                'materials' => 0.0,
                'works' => $assemblyData['gluing'],
            ],
            'assembly_insert' => [
                'materials' => 0.0,
                'works' => $assemblyData['insert'],
            ],
            'assembly_cord_cutting' => [
                'materials' => 0.0,
                'works' => $assemblyData['cord_cutting'],
            ],
            'assembly_glue' => [
                'materials' => 0.0,
                'works' => $assemblyData['glue'],
            ],
            'bottom' => [
                'materials' => 0.0,
                'works' => $bottomData,
            ],
            'luvers' => [
                'materials' => 0.0,
                'works' => $luversTotal,
            ],
            'packaging' => [
                'materials' => $packagingTotal,
                'works' => 0.0,
            ],
        ];

        foreach ($components as $key => $component) {
            $components[$key]['total'] = $component['materials'] + $component['works'];
        }

        $resultMaterials = array_sum(array_column($components, 'materials'));
        $resultWorks = array_sum(array_column($components, 'works'));
        $subtotal = $resultMaterials + $resultWorks;
        $markupPercent = (float) config("calculator.markup_percent.{$input->typeOrder}", 0);
        $resultCirculation = $subtotal * (1 + ($markupPercent / 100));
        $pricePerBag = $input->paperCirculation > 0 ? $resultCirculation / $input->paperCirculation : 0.0;

        $metrics = [
            'paper_price' => round($paperPrice, 2),
            'paper_sheet_count' => round($paperSheets, 2),
            'paper_prebuild_sheets' => round($printPrebuildSheets, 2),
            'paper_sheets_in_work' => round($paperSheetsInWork, 2),
            'paper_total' => round($paperTotal, 2),

            'offset_total' => round($offsetData['total'], 2),
            'silk_total' => round($silkData['total'], 2),
            'uv_lac_total' => round($uvLacData['total'], 2),
            'lamination_total' => round($laminationData['total'], 2),
            'die_cutting_total' => round($dieCuttingData['total'], 2),
            'cutting_total' => round($cuttingTotal, 2),
            'handle_total' => round($handleData['total'], 2),
            'assembly_total' => round($assemblyData['total'], 2),
            'bottom_total' => round($bottomData, 2),
            'luvers_total' => round($luversTotal, 2),
            'packaging_total' => round($packagingTotal, 2),

            'result_materials' => round($resultMaterials, 2),
            'result_works' => round($resultWorks, 2),
            'subtotal' => round($subtotal, 2),
            'markup_percent' => round($markupPercent, 2),
            'result_circulation' => round($resultCirculation, 2),
            'price_per_bag' => round($pricePerBag, 2),

            'paper_source' => $paper ? $this->paperSourceLabel($paper) : 'Бумага не выбрана',
            'offset_source' => $offset ? $this->offsetSourceLabel($offset) : 'Офсет не выбран',
            'uv_lac_source' => $uvLacData['source'],
            'lamination_source' => $laminationData['source'],
            'handle_source' => $handleData['source'],
        ];

        $debug = $this->buildDebug(
            input: $input,
            size: $size,
            group: $group,
            paper: $paper,
            offset: $offset,
            sizeFormat: $sizeFormat,
            paperFormat: $paperFormat,
            paperSheets: $paperSheets,
            printPrebuildSheets: $printPrebuildSheets,
            paperSheetsInWork: $paperSheetsInWork,
            paperTotal: $paperTotal,
            offsetData: $offsetData,
            silkData: $silkData,
            uvLacData: $uvLacData,
            laminationData: $laminationData,
            dieCuttingData: $dieCuttingData,
            cuttingTotal: $cuttingTotal,
            handleData: $handleData,
            assemblyData: $assemblyData,
            bottomData: $bottomData,
            luversTotal: $luversTotal,
            packagingTotal: $packagingTotal,
            resultMaterials: $resultMaterials,
            resultWorks: $resultWorks,
            subtotal: $subtotal,
            markupPercent: $markupPercent,
            resultCirculation: $resultCirculation,
            pricePerBag: $pricePerBag,
        );

        return new BagCalculationResult($metrics, $components, $debug);
    }

    private function resolveFormatPrint(?Size $size): string
    {
        if (! $size?->type) {
            return 'A2';
        }

        return in_array($size->type, ['A1', 'A2', 'A3'], true) ? $size->type : 'A2';
    }

    private function resolvePaperFormat(?Size $size): string
    {
        $format = strtoupper(trim((string) ($size?->paper_format ?? '')));

        if (in_array($format, ['A', 'B'], true)) {
            return $format;
        }

        $paperSize = strtolower((string) ($size?->size_paper ?? ''));
        $paperSize = preg_replace('/\s+/', '', $paperSize) ?? '';

        return match (true) {
            preg_match('/^(620[\*x]940|940[\*x]620)$/', $paperSize) === 1 => 'A',
            preg_match('/^(720[\*x]1040|1040[\*x]720|700[\*x]1000|1000[\*x]700)$/', $paperSize) === 1 => 'B',
            default => 'A',
        };
    }

    private function paperFormatDivisor(?Size $size): int
    {
        return $this->resolvePaperFormat($size) === 'B' ? 2 : 1;
    }

    private function resolvePaperPrice(?PaperPrice $paper): float
    {
        if (! $paper) {
            return 0.0;
        }

        $salePrice = (float) ($paper->sale_price ?? 0);

        if ($salePrice > 0) {
            return $salePrice;
        }

        $basePrice = (float) ($paper->base_price ?? 0);
        $markup = (float) ($paper->markup_percent ?? 0);

        return $basePrice > 0 ? $basePrice * (1 + ($markup / 100)) : 0.0;
    }

    private function calculatePaperSheets(BagCalculationInput $input, ?Size $size, int $printPrebuildSheets): float
    {
        if ($input->paperCirculation <= 0) {
            return 0.0;
        }

        $baseSheets = $input->paperCirculation + 20;
        $sheetsBeforeFormat = ($baseSheets * 1.04) + $printPrebuildSheets;

        // A/B — это не размер пакета, а база печатного листа: B переводим в большой лист делением на 2.
        return $this->roundUp($sheetsBeforeFormat / $this->paperFormatDivisor($size), 25);
    }

    private function calculatePrintPrebuildSheets(BagCalculationInput $input): int
    {
        $offsetSheets = 0;
        $silkSheets = 0;

        if (in_array('ofset', $input->printType, true)) {
            $offsetColorCount = $this->parseColorPair($this->selectedOffsetColors($input));
            $offsetSheets = $offsetColorCount * 50;
        }

        if (in_array('selkografiia', $input->printType, true)) {
            $silkSheets = 0;
        }

        $plashkaSheets = $input->printPlashka ? 50 : 0;

        return $offsetSheets + $silkSheets + $plashkaSheets;
    }

    private function calculateOffsetPrint(BagCalculationInput $input, ?Ofset $offset, ?PaperPrice $paper, ?Size $size): array
    {
        if (! in_array('ofset', $input->printType, true) || ! $offset || $input->paperCirculation <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Офсет не выбран',
            ];
        }

        $sheetCount = $input->paperCirculation + 20;
        $isMelPaper = $this->isMelPaper($paper);
        $sheetPrice = (float) ($isMelPaper ? ($offset->sale_print_mel_paper ?? 0) : ($offset->sale_print ?? 0));
        $setup = (float) ($offset->sale_preparation ?? 0);
        $total = ($sheetCount * $sheetPrice) + $setup;

        return [
            'materials' => 0.0,
            'works' => $total,
            'total' => $total,
            'source' => $this->offsetSourceLabel($offset),
        ];
    }

    private function calculateSilkPrint(BagCalculationInput $input, float $printSheets): array
    {
        if (! in_array('selkografiia', $input->printType, true) || $printSheets <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Шелкография не выбрана',
            ];
        }

        $colorCount = max(0, (int) ($input->silkColorCount ?? 0));

        // Производственная логика клиента: 12 руб за цвет на лист + 1200 руб приладка на цвет.
        $total = ($printSheets * 12 * $colorCount) + (1200 * $colorCount);
        $total = max($total, 3000.0);

        return [
            'materials' => 0.0,
            'works' => $total,
            'total' => $total,
            'source' => $colorCount > 0 ? "Шелкография {$colorCount} цвет(а/ов)" : 'Шелкография не выбрана',
        ];
    }

    private function calculateUvLac(BagCalculationInput $input, ?Size $size, string $sizeFormat, float $paperSheets): array
    {
        if (! in_array('uf-lak', $input->postPrintType, true) || $paperSheets <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'УФ-лак не выбран',
            ];
        }

        $runCount = (int) ceil($paperSheets);

        $processType = 'Выборочное';

        $row = LacPrice::query()
            ->where('min_run', '<=', $runCount)
            ->where('max_run', '>=', $runCount)
            ->where('lacquer_type', 'Матовая')
            ->where('process_type', $processType)
            ->where('format', $sizeFormat)
            ->first();

        if (! $row) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Подходящая строка УФ-лака не найдена',
            ];
        }

        $price = (float) ($row->price ?? 0);

        // Первая ступень в прайсе — это минимальная сумма за запуск, а не цена за лист.
        // Для остальных ступеней цена считается за каждый лист.
        $preSale = ((int) $row->min_run <= 400 && (int) $row->max_run <= 400)
            ? $price
            : $price * $paperSheets;
        $dryingPerSheet = match ($sizeFormat) {
            'A1' => 5.0,
            'A2' => 3.0,
            'A3' => 2.5,
            default => 3.0,
        };

        $total = $preSale + ($dryingPerSheet * $paperSheets);

        if ($input->printOptionDischarge) {
            $dischargePerSheet = match ($sizeFormat) {
                'A1' => 9.0,
                'A2' => 5.0,
                'A3' => 3.0,
                default => 5.0,
            };

            $total += $dischargePerSheet * $paperSheets;
        }

        return [
            'materials' => 0.0,
            'works' => $total,
            'total' => $total,
            'source' => $this->uvLacSourceLabel($row),
        ];
    }

    private function calculateLamination(BagCalculationInput $input, ?Size $size, float $paperSheets): array
    {
        if (! in_array('lamination', $input->printOptions, true) || ! $size || $paperSheets <= 0 || ! $input->typeLamination) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Ламинация не выбрана',
            ];
        }

        $category = Category::query()->where('slug', $input->typeLamination)->first();
        if (! $category) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Ламинация не найдена в категориях',
            ];
        }

        $product = Product::query()->where('slug', 'laminaciia-' . $category->slug)->first();
        $salePerM2 = (float) ($product?->price ?? 0);
        $areaM2 = $this->paperAreaM2($size);
        $materialsPerSheet = $areaM2 * $salePerM2;
        $materials = $materialsPerSheet * $paperSheets;

        $workPerSheet = 2.5;
        $prebuild = 150.0;
        $works = ($workPerSheet * $paperSheets) + $prebuild;
        $total = $materials + $works;

        return [
            'materials' => $materials,
            'works' => $works,
            'total' => $total,
            'source' => $product
                ? $product->name . ' / ' . number_format($salePerM2, 2, '.', ' ')
                : 'Ламинация не найдена',
        ];
    }

    private function calculateDieCutting(BagCalculationInput $input, ?Size $size, float $paperSheets): array
    {
        if (! $size || $input->paperCirculation <= 0) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
            ];
        }

        $sheetCount = ($input->paperCirculation + 20) * 1.04;
        $total = ($sheetCount * 0.75) + 700;

        return [
            'materials' => 0.0,
            'works' => $total,
            'total' => $total,
        ];
    }

    private function calculateCutting(BagCalculationInput $input): float
    {
        if ($input->paperCirculation <= 0) {
            return 0.0;
        }

        if ($input->paperCirculation <= 300) {
            return 100.0;
        }

        if ($input->paperCirculation <= 1000) {
            return 200.0;
        }

        return round($input->paperCirculation * 0.25, 2);
    }

    private function calculateHandle(BagCalculationInput $input, ?Size $size, ?Group $group, ?Category $handleCategory, ?Category $bracingCategory): array
    {
        if (! in_array('handle', $input->printOptions, true) || ! $size || ! $group || $input->paperCirculation <= 0 || ! $input->typeHandleId) {
            return [
                'materials' => 0.0,
                'works' => 0.0,
                'total' => 0.0,
                'source' => 'Ручка не выбрана',
            ];
        }

        $handleProduct = Product::query()->where('slug', $handleCategory?->slug)->first();
        $materialPrice = (float) ($handleProduct?->price ?? 0);
        $lengthCord = (float) ($size->length_cord ?? 0);

        $worksPerUnit = 0.0;

        if ((string) ($handleCategory?->slug ?? '') !== '' && str_contains((string) $handleCategory->slug, 'snur')) {
            $worksPerUnit += (float) ($group->hole ?? 0);
        }

        if ($input->typeBracingHandle) {
            $worksPerUnit += (float) config('calculator.bracing_handle_prices.' . $input->typeBracingHandle, 0);
        }

        if ($input->handleX2) {
            $worksPerUnit *= 2;
        }

        $materials = ($lengthCord * $materialPrice) * $input->paperCirculation;
        $works = $worksPerUnit * $input->paperCirculation;

        if ($input->handleX2) {
            $materials *= 2;
            $works *= 2;
        }

        $total = $materials + $works;

        return [
            'materials' => $materials,
            'works' => $works,
            'total' => $total,
            'source' => $this->handleSourceLabel($handleCategory, $materialPrice),
        ];
    }

    private function calculateAssembly(BagCalculationInput $input, ?Size $size, ?Group $group): array
    {
        if (! $size || ! $group || $input->paperCirculation <= 0) {
            return [
                'gluing' => 0.0,
                'insert' => 0.0,
                'cord_cutting' => 0.0,
                'glue' => 0.0,
                'total' => 0.0,
            ];
        }

        $circulation = $input->paperCirculation;

        $gluing = (float) ($group->boking_gluing ?? 0) * $circulation;
        $insert = (float) ($group->sidewall ?? 0) * $circulation;
        $cordCutting = (float) ($group->cutting_cord_2 ?? 0) * $circulation;
        $glue = 0.085 * 30 * $circulation;
        $total = $gluing + $insert + $cordCutting + $glue;

        return [
            'gluing' => $gluing,
            'insert' => $insert,
            'cord_cutting' => $cordCutting,
            'glue' => $glue,
            'total' => $total,
        ];
    }

    private function calculateBottom(BagCalculationInput $input, ?Size $size, ?Group $group): float
    {
        if (! $size || $input->paperCirculation <= 0) {
            return 0.0;
        }

        $perUnit = (float) ($group?->bottom ?? 0);

        if ($perUnit <= 0) {
            $perUnit = match ($size->type) {
                'A5', 'A4' => 1.0,
                'A3' => 4.2,
                'A2' => 5.0,
                default => 0.0,
            };
        }

        return $perUnit * $input->paperCirculation;
    }

    private function calculatePackaging(BagCalculationInput $input, ?Size $size): float
    {
        if (! $size || $input->paperCirculation <= 0 || ! $size->package) {
            return 0.0;
        }

        $boxes = (int) ceil($input->paperCirculation / (float) $size->package);
        $boxPrice = (float) (Product::query()->find(config('calculator.products.package_box'))?->price ?? 0);

        return $boxes * $boxPrice;
    }

    private function calculateLuvers(BagCalculationInput $input, ?Group $group): float
    {
        if (! $input->luversEnabled || ! $group || $input->paperCirculation <= 0) {
            return 0.0;
        }

        return (float) ($group->luvers ?? 0) * $input->paperCirculation;
    }

    private function paperAreaM2(Size $size): float
    {
        if (! $size->membrane || ! $size->length_membrane) {
            return 0.0;
        }

        return ((float) $size->membrane / 1000) * (float) $size->length_membrane;
    }

    private function selectedOffsetColors(BagCalculationInput $input): ?string
    {
        if (! $input->offsetPriceId) {
            return null;
        }

        return Ofset::query()->find($input->offsetPriceId)?->colors;
    }

    private function isMelPaper(?PaperPrice $paper): bool
    {
        $value = mb_strtolower(trim((string) ($paper?->title ?? '') . ' ' . (string) ($paper?->group_name ?? '')));

        return str_contains($value, 'mel');
    }

    private function parseColorPair(?string $value): int
    {
        if (! $value) {
            return 0;
        }

        if (str_contains($value, '+')) {
            return array_sum(array_map(static fn ($part) => (int) trim((string) $part), explode('+', $value)));
        }

        return (int) $value;
    }

    private function roundUp(float $value, int $step): float
    {
        if ($value <= 0 || $step <= 0) {
            return 0.0;
        }

        return ceil($value / $step) * $step;
    }

    private function paperSourceLabel(PaperPrice $paper): string
    {
        $sale = $this->resolvePaperPrice($paper);

        return trim($paper->group_name . ' / ' . $paper->title . ' / ' . $paper->sheet_format . ' / ' . number_format($sale, 2, '.', ' '));
    }

    private function offsetSourceLabel(Ofset $offset): string
    {
        return trim(
            (string) ($offset->colors ?? '-') .
            ' / подготовка ' . number_format((float) ($offset->sale_preparation ?? 0), 2, '.', ' ') .
            ' / печать ' . number_format((float) ($offset->sale_print ?? 0), 2, '.', ' ') .
            ' / мел ' . number_format((float) ($offset->sale_print_mel_paper ?? 0), 2, '.', ' ')
        );
    }

    private function uvLacSourceLabel(LacPrice $row): string
    {
        return trim(
            (string) $row->lacquer_type .
            ' / ' . (string) $row->process_type .
            ' / ' . (string) $row->format .
            ' / ' . number_format((float) ($row->price ?? 0), 2, '.', ' ') .
            ' / ' . (int) $row->min_run . '-' . (int) $row->max_run
        );
    }

    private function handleSourceLabel(?Category $category, float $price): string
    {
        if (! $category) {
            return 'Ручка не выбрана';
        }

        return trim($category->name . ' / ' . $category->slug . ' / ' . number_format($price, 2, '.', ' '));
    }

    /**
     * @return array<string, string>
     */
    private function buildDebug(
        BagCalculationInput $input,
        ?Size $size,
        ?Group $group,
        ?PaperPrice $paper,
        ?Ofset $offset,
        string $sizeFormat,
        string $paperFormat,
        float $paperSheets,
        int $printPrebuildSheets,
        float $paperSheetsInWork,
        float $paperTotal,
        array $offsetData,
        array $silkData,
        array $uvLacData,
        array $laminationData,
        array $dieCuttingData,
        float $cuttingTotal,
        array $handleData,
        array $assemblyData,
        float $bottomData,
        float $luversTotal,
        float $packagingTotal,
        float $resultMaterials,
        float $resultWorks,
        float $subtotal,
        float $markupPercent,
        float $resultCirculation,
        float $pricePerBag,
    ): array {
        return [
            'summary' => implode("\n", [
                'ИТОГО_Материалы: ' . number_format($resultMaterials, 2, '.', ' '),
                'ИТОГО_Работы: ' . number_format($resultWorks, 2, '.', ' '),
                'ИТОГО_Себестоимость: ' . number_format($subtotal, 2, '.', ' '),
                'ИТОГО_Наценка_%: ' . number_format($markupPercent, 2, '.', ' '),
                'ИТОГО_К_Выставлению: ' . number_format($resultCirculation, 2, '.', ' '),
                'ИТОГО_Цена_1_пакета: ' . number_format($pricePerBag, 2, '.', ' '),
            ]),
            'nodes' => implode("\n\n", [
                '[СУММИРУЕМЫЕ УЗЛЫ]',
                'Бумага: ' . number_format($paperTotal, 2, '.', ' '),
                'Офсет: ' . number_format((float) ($offsetData['total'] ?? 0), 2, '.', ' '),
                'Шелкография: ' . number_format((float) ($silkData['total'] ?? 0), 2, '.', ' '),
                'УФ-лак: ' . number_format((float) ($uvLacData['total'] ?? 0), 2, '.', ' '),
                'Ламинация: ' . number_format((float) ($laminationData['total'] ?? 0), 2, '.', ' '),
                'Вырубка: ' . number_format((float) ($dieCuttingData['total'] ?? 0), 2, '.', ' '),
                'Резка: ' . number_format($cuttingTotal, 2, '.', ' '),
                'Ручка: ' . number_format((float) ($handleData['total'] ?? 0), 2, '.', ' '),
                'Склейка: ' . number_format((float) ($assemblyData['total'] ?? 0), 2, '.', ' '),
                'Дно: ' . number_format($bottomData, 2, '.', ' '),
                'Люверс: ' . number_format($luversTotal, 2, '.', ' '),
                'Упаковка: ' . number_format($packagingTotal, 2, '.', ' '),
                '',
                '[СПРАВОЧНО, НЕ СУММИРОВАТЬ]',
                'Формат печати: ' . $sizeFormat,
                'Формат бумаги A/B: ' . $paperFormat,
                'Бумага: ' . ($paper ? $this->paperSourceLabel($paper) : '-'),
                'Офсет: ' . ($offset ? $this->offsetSourceLabel($offset) : '-'),
                'Приладка печати: ' . $printPrebuildSheets,
                'Листов бумаги в работе: ' . number_format($paperSheetsInWork, 2, '.', ' '),
                'Листов бумаги по базе: ' . number_format($paperSheets, 2, '.', ' '),
                'Размер формы: ' . ($size?->number ?? '-'),
                'Группа: ' . ($group?->group_name ?? '-'),
            ]),
            'formulas' => implode("\n\n", [
                '[БУМАГА]',
                'округлить_вверх_до_25((((тираж + 20) * 1.04) + приладка_печати) / делитель_A_B)',
                '[ПЕЧАТЬ ОФСЕТ]',
                '(тираж + 20) * цена_листа + подготовка',
                '[ШЕЛКОГРАФИЯ]',
                '(тираж + 20) * 12 * цвета + 1200 * цвета, минимум 3000',
                '[УФ-ЛАК]',
                'таблица price_lacs + сушка + коронирование',
                '[ЛАМИНАЦИЯ]',
                'площадь_листа * цена_м2 * листы + 2.5 * листы + 150',
                '[ВЫРУБКА]',
                '((тираж + 20) * 1.04) * 0.75 + 700',
                '[РЕЗКА]',
                '100 до 300, 200 до 1000, далее 0.25 за штуку',
                '[СБОРКА]',
                'склейка + вставка + резка шнура + клей + дно + люверсы + упаковка',
            ]),
            'inputs' => implode("\n", [
                'Тираж: ' . $input->paperCirculation,
                'Бумага ID: ' . ($input->paperPriceId ?? '-'),
                'Форма ID: ' . ($input->sizeId ?? '-'),
                'Тип заказа: ' . ($input->typeOrder ?? '-'),
                'Печать: ' . implode(', ', $input->printType ?: ['-']),
                'Опции: ' . implode(', ', $input->printOptions ?: ['-']),
                'Офсет ID: ' . ($input->offsetPriceId ?? '-'),
                'Шелкография: ' . ($input->silkColorCount ?? '-'),
                'Постпечатка: ' . implode(', ', $input->postPrintType ?: ['-']),
                'Ламинация: ' . ($input->typeLamination ?? '-'),
                'Ручка ID: ' . ($input->typeHandleId ?? '-'),
                'Крепление ручки: ' . ($input->typeBracingHandle ?? '-'),
                'Ручка х2: ' . ($input->handleX2 ? 'да' : 'нет'),
            ]),
        ];
    }
}
