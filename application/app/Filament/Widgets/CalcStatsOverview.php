<?php

namespace App\Filament\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CalcStatsOverview extends StatsOverviewWidget
{
    use InteractsWithForms;

    protected ?string $pollingInterval = '2s';

    protected function getStats(): array
    {
        return [
//            Stat::make('Итог на бумагу', 'sda'),
//            Stat::make('Стоимость печати', '21%'),
//            Stat::make('Итого за пантоны', '3:12'),
        ];
    }
}
