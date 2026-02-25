<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\App\Widgets\CalcStatsOverview;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CalcStatsOverview::class,
        ];
    }




    public function form(Schema $schema): Schema
    {
        return parent::form($schema);
    }

    protected function afterCreate(): void
    {
        /** @var Order $order */
        $order = $this->record;

        /** @var User $user */
        $user = auth()->user();

        Notification::make()
            ->title('New order')
            ->icon('heroicon-o-shopping-bag')
            ->body("**{$order->customer?->name} ordered {$order->items->count()} products.**")
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($user);
    }

    /**
     * @return array<Step>
     */
    protected function getSteps(): array
    {
        return [
            Step::make('База')
                ->schema([
                    Section::make()
                        ->schema(OrderForm::getDetailsComponents())
                        ->columns(),
                ]),

            Step::make('Опции')
                ->schema([
                    Section::make()
                        ->schema([OrderForm::getItemsRepeater()])
                        ->schema([]),
                ]),
        ];
    }
}
