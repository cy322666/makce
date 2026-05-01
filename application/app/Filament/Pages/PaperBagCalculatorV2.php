<?php

namespace App\Filament\Pages;

use App\Domain\BagCalculatorV2\BagCalculationInput;
use App\Domain\BagCalculatorV2\BagCalculator;
use App\Filament\Pages\PaperBagCalculatorV2\Schemas\PaperBagCalculatorV2Form;
use BackedEnum;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class PaperBagCalculatorV2 extends Page
{
    protected static ?string $slug = 'calculator-v2';

    protected static ?string $title = 'Калькулятор пакета v2';

    protected static ?string $navigationLabel = 'Калькулятор v2';

    protected static string | UnitEnum | null $navigationGroup = 'Калькуляторы';

    protected static ?int $navigationSort = 1;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    public ?array $data = [];
    public array $calculation = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->syncCalculation();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form'),
            ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return PaperBagCalculatorV2Form::configure($schema);
    }

    public function syncCalculation(): void
    {
        /** @var BagCalculator $calculator */
        $calculator = app(BagCalculator::class);

        $this->calculation = [
            'metrics' => [],
            'components' => [],
            'debug' => [],
        ];

        try {
            $result = $calculator->calculate(BagCalculationInput::fromState($this->data ?? []));

            $this->calculation = [
                'metrics' => $result->metrics,
                'components' => $result->components,
                'debug' => $result->debug,
            ];
        } catch (\Throwable $throwable) {
            $this->calculation = [
                'metrics' => [],
                'components' => [],
                'debug' => [
                    'summary' => 'Ошибка пересчета: ' . $throwable->getMessage(),
                    'nodes' => '',
                    'formulas' => '',
                    'inputs' => '',
                ],
            ];
        }
    }
}
