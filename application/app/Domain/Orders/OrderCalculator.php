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
    /**
     * Главная точка расчета заказа.
     * Возвращает:
     * - metrics: плоские значения для формы/сохранения;
     * - components: детализацию по этапам (materials/works/total).
     */
    public function calculate(OrderCalculationInput $input): OrderCalculationResult
    {
        // Подтягиваем основные справочники для формы.
        $size = $input->sizeId ? Size::query()->find($input->sizeId) : null;
        $group = $size ? Group::query()->where('number_id', $size->number)->first() : null;

        // Базовые производные параметры, которые участвуют в нескольких формулах.
        $sizeFormat = $this->resolveFormatPrint($size);
        $sale1Channel = $size?->channel ? (float) $size->channel : 0.0;
        $sale1Page = $this->resolveSale1Paper($input, $size);

        // Блок бумаги: тиражные листы/приладки/кол-во листов в работу.
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

        // Единая структура breakdown: отдельно материалы и работы по этапам.
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

        // Общие итоги: сначала себестоимость, затем наценка по типу заказа.
        $resultMaterials = array_sum(array_column($components, 'materials'));
        $resultWorks = array_sum(array_column($components, 'works'));
        $subtotal = $resultMaterials + $resultWorks;

        $markupPercent = $this->resolveMarkupPercent($input->typeOrder);
        $resultCirculation = $subtotal * (1 + ($markupPercent / 100));

        $debugData = $this->buildDebugData(
            input: $input,
            size: $size,
            group: $group,
            sizeFormat: $sizeFormat,
            sale1Page: $sale1Page,
            sale1Channel: $sale1Channel,
            printPrebuild: $printPrebuild,
            paperPackWork: $paperPackWork,
            countPaper: $countPaper,
            printPagesCirculation: $printPagesCirculation,
            paperResult: $paperResult,
            printSaleResult: $printSaleResult,
            postPrintSale: $postPrintSale,
            laminationData: $laminationData,
            fellingData: $fellingData,
            assemblyResult: $assemblyResult,
            assemblyResultCirculation: $assemblyResultCirculation,
            handleData: $handleData,
            packagesResult: $packagesResult,
            resultMaterials: $resultMaterials,
            resultWorks: $resultWorks,
            subtotal: $subtotal,
            markupPercent: $markupPercent,
            resultCirculation: $resultCirculation,
        );

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

            // Отладочные поля для визуальной проверки логики расчета в форме.
            'debug_nodes' => $debugData['nodes'],
            'debug_formulas' => $debugData['formulas'],
            'debug_inputs' => $debugData['inputs'],
            'debug_summary' => $debugData['summary'],
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

        // Пакетов в работу = тираж с технологическим запасом + 1/2 листов на приладку печати.
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

        // Переводим «пакеты в работу» в листы печатного формата и умножаем на цену листа.
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

        // Собираем slug сырья из типа бумаги + граммовки + формата листа.
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

        $baseSlug = $paperPrefix.'-'.$weight;
        $candidateSlugs = [];

        $suffix = config("calculator.size_paper_suffix_map.{$size->size_paper}");
        if ($suffix) {
            // Сначала пробуем размеро-специфичный прайс, как в старом Excel.
            $candidateSlugs[] = $baseSlug.'-'.$suffix;
        }

        // Фолбэк: если нет прайса под конкретный формат листа, берем общий прайс бумаги.
        $candidateSlugs[] = $baseSlug;

        foreach ($candidateSlugs as $slug) {
            $price = (float) (Product::query()->where('slug', $slug)->first()?->price ?? 0);
            if ($price > 0) {
                return $price;
            }
        }

        return 0;
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

        // Берем ближайшую ценовую ступень из таблицы офсета.
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

        // База по прайсу лака + сушка + опционально коронирование.
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

        // Материалы: стоимость пленки на 1 изделие.
        $membraneMaterials = $this->calculateMembraneCost($size, $sale);

        // Работы: приладка + работа по листам.
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
        // Удары = листы * число заготовок в форме.
        $countBlows = $printPagesCirculation * (float) $size->count_blank;

        // Материалы: каналы; работы: удары + приладки вырубки.
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

        // Базовая сборка 1 пакета по группе формы.
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

        // Работы по ручке на 1 изделие.
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

        // Материалы ручки считаем отдельно от работ.
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

        // Приладка по печати складывается из офсета/шелкографии/плашки.
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

        // Поддержка форматов вида "2+1" и "3".
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

    /**
     * Готовит человекочитаемую трассировку:
     * - inputs: что пришло на вход;
     * - nodes: основные узлы с суммами;
     * - formulas: ключевые формулы с подстановкой;
     * - summary: финальные итоги.
     */
    private function buildDebugData(
        OrderCalculationInput $input,
        ?Size $size,
        ?Group $group,
        string $sizeFormat,
        float $sale1Page,
        float $sale1Channel,
        int $printPrebuild,
        float $paperPackWork,
        float $countPaper,
        float $printPagesCirculation,
        float $paperResult,
        float $printSaleResult,
        float $postPrintSale,
        array $laminationData,
        array $fellingData,
        float $assemblyResult,
        float $assemblyResultCirculation,
        array $handleData,
        float $packagesResult,
        float $resultMaterials,
        float $resultWorks,
        float $subtotal,
        float $markupPercent,
        float $resultCirculation,
    ): array {
        $inputs = [
            'Размер формы' => $size?->number ?? '-',
            'Тип размера' => $size?->type ?? '-',
            'Формат печати' => $sizeFormat !== '' ? $sizeFormat : '-',
            'Тираж' => $input->paperCirculation,
            'Тип бумаги ID' => $input->typePaperId ?? '-',
            'Тип заказа' => $input->typeOrder ?? '-',
            'Опции печати' => $this->implodeOrDash($input->printOptions),
            'Тип печати' => $this->implodeOrDash($input->printType),
            'Офсет 1' => $input->printTypeOfset ?? '-',
            'Офсет 2' => $input->printTypeOfset2 ?? '-',
            'Шелкография' => $input->printTypeSelkografiia ?? '-',
            'Постпечатка' => $this->implodeOrDash($input->postPrintType),
            'Ламинация' => $input->typeLamination ?? '-',
            'Плашка' => $input->printPlashka ? 'да' : 'нет',
            'Коронирование' => $input->printOptionDischarge ? 'да' : 'нет',
            'Тип ручки ID' => $input->typeHandleId ?? '-',
            'Крепление ручки' => $input->typeBracingHandle ?? '-',
            'Ручка x2' => $input->handleX2 ? 'да' : 'нет',
            'Группа формы найдена' => $group ? 'да' : 'нет',
            'Цена листа (sale_1_page)' => $this->fmt($sale1Page),
            'Цена канала (sale_1_channel)' => $this->fmt($sale1Channel),
        ];

        $nodes = implode("\n", [
            '[СУММИРУЕМЫЕ УЗЛЫ]',
            'БУМАГА_Сумма: '.$this->fmt($paperResult),
            'ОФСЕТ_Сумма: '.$this->fmt($printSaleResult),
            'УФ_Сумма: '.$this->fmt($postPrintSale),
            'ЛАМИНАЦИЯ_Сумма: '.$this->fmt((float) ($laminationData['total'] ?? 0)),
            'ВЫРУБКА_Сумма: '.$this->fmt((float) ($fellingData['total'] ?? 0)),
            'РЕЗКА_Сумма: '.$this->fmt($assemblyResult),
            'СКЛЕЙКА_Сумма: '.$this->fmt($assemblyResultCirculation),
            'РУЧКИ_Сумма: '.$this->fmt((float) ($handleData['total'] ?? 0)),
            'КОРОБКИ_Сумма: '.$this->fmt($packagesResult),
            'ИТОГО_Себестоимость: '.$this->fmt($subtotal),
            'ПАКЕТ_Сумма: '.$this->fmt($resultCirculation),
            '',
            '[СПРАВОЧНО, НЕ СУММИРОВАТЬ]',
            'БУМАГА_ЛистовПриладка: '.$this->fmt($printPrebuild),
            'БУМАГА_ПакетовВРаботу: '.$this->fmt($paperPackWork),
            'БУМАГА_ЛистовВРаботу: '.$this->fmt($countPaper),
            'ПЕЧАТЬ_ЛистовТиража: '.$this->fmt($printPagesCirculation),
            '',
            '[РАЗБИВКА ПО СОСТАВУ]',
            'ЛАМИНАЦИЯ_Материалы: '.$this->fmt((float) ($laminationData['materials'] ?? 0)),
            'ЛАМИНАЦИЯ_Работы: '.$this->fmt((float) ($laminationData['works'] ?? 0)),
            'ВЫРУБКА_Материалы: '.$this->fmt((float) ($fellingData['materials'] ?? 0)),
            'ВЫРУБКА_Работы: '.$this->fmt((float) ($fellingData['works'] ?? 0)),
            'РУЧКИ_Материалы: '.$this->fmt((float) ($handleData['materials'] ?? 0)),
            'РУЧКИ_Работы: '.$this->fmt((float) ($handleData['works'] ?? 0)),
            '',
            '[ИТОГИ]',
            'ИТОГО_Материалы: '.$this->fmt($resultMaterials),
            'ИТОГО_Работы: '.$this->fmt($resultWorks),
            'ИТОГО_Наценка_%: '.$this->fmt($markupPercent),
            'ПАКЕТ_Цена: '.$this->fmt($input->paperCirculation > 0 ? $resultCirculation / $input->paperCirculation : 0),
        ]);

        $formulas = [
            'paper_pack_work' => '((тираж / 100) * waste_percent) + (print_prebuild / 2) = '
                .$this->fmt((($input->paperCirculation / 100) * (float) config('calculator.paper.waste_percent', 108)))
                .' + '.$this->fmt($printPrebuild / 2).' = '.$this->fmt($paperPackWork),
            'paper_result' => '(paper_pack_work / (count_1 / 2)) * sale_1_page = '.$this->fmt($paperResult),
            'print_pages_circulation' => 'по типоразмеру = '.$this->fmt($printPagesCirculation),
            'lamination_total' => 'materials + works = '
                .$this->fmt((float) ($laminationData['materials'] ?? 0)).' + '
                .$this->fmt((float) ($laminationData['works'] ?? 0)).' = '
                .$this->fmt((float) ($laminationData['total'] ?? 0)),
            'felling_total' => 'materials + works = '
                .$this->fmt((float) ($fellingData['materials'] ?? 0)).' + '
                .$this->fmt((float) ($fellingData['works'] ?? 0)).' = '
                .$this->fmt((float) ($fellingData['total'] ?? 0)),
            'result_circulation' => '(materials + works) * (1 + markup/100) = '
                .$this->fmt($subtotal).' * '.(1 + ($markupPercent / 100)).' = '.$this->fmt($resultCirculation),
        ];

        $summary = [
            'Себестоимость (материалы)' => $this->fmt($resultMaterials),
            'Себестоимость (работы)' => $this->fmt($resultWorks),
            'Себестоимость (всего)' => $this->fmt($subtotal),
            'Наценка %' => $this->fmt($markupPercent),
            'Итог за тираж' => $this->fmt($resultCirculation),
            'Итог за 1 пакет' => $this->fmt($input->paperCirculation > 0 ? $resultCirculation / $input->paperCirculation : 0),
        ];

        return [
            'inputs' => $this->lines($inputs),
            'nodes' => $nodes,
            'formulas' => $this->lines($formulas),
            'summary' => $this->lines($summary),
        ];
    }

    private function lines(array $data): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            $lines[] = $key.': '.$value;
        }

        return implode("\n", $lines);
    }

    private function fmt(float|int $value): string
    {
        return number_format((float) $value, 2, '.', ' ');
    }

    private function implodeOrDash(array $values): string
    {
        if ($values === []) {
            return '-';
        }

        return implode(', ', $values);
    }
}
