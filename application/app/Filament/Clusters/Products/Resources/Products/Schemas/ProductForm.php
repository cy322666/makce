<?php

namespace App\Filament\Clusters\Products\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    //TODO
    //стоимость работ
    //стоимость материалов

    //тип бумаги
    //размер
    //стоимость

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                        if ($operation !== 'create') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                TextInput::make('price')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),

//                                TextInput::make('size')
//                                    ->label('Размер')
//                                    ->required(),
                            ])
                            ->columns(2),

//                        Section::make('Pricing')
//                            ->schema([
//
//                            ])
//                            ->columns(2),

//                        Section::make('Inventory')
//                            ->schema([
//                                TextInput::make('sku')
//                                    ->label('SKU (Stock Keeping Unit)')
//                                    ->unique(Product::class, 'sku', ignoreRecord: true)
//                                    ->maxLength(255)
//                                    ->required(),
//
//                                TextInput::make('barcode')
//                                    ->label('Barcode (ISBN, UPC, GTIN, etc.)')
//                                    ->unique(Product::class, 'barcode', ignoreRecord: true)
//                                    ->maxLength(255)
//                                    ->required(),
//
//                                TextInput::make('qty')
//                                    ->label('Quantity')
//                                    ->numeric()
//                                    ->rules(['integer', 'min:0'])
//                                    ->required(),
//
//                                TextInput::make('security_stock')
//                                    ->helperText('The safety stock is the limit stock for your products which alerts you if the product stock will soon be out of stock.')
//                                    ->numeric()
//                                    ->rules(['integer', 'min:0'])
//                                    ->required(),
//                            ])
//                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visibility')
                                    ->helperText('This product will be hidden from all sales channels.')
                                    ->default(true),

                                DatePicker::make('published_at')
                                    ->label('Publishing date')
                                    ->default(now())
                                    ->required(),

//                                Select::make('categories')
//                                    ->relationship('categories', 'name')
//                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
