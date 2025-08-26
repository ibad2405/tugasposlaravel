<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Customer Information')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->maxLength(255),
                        ])
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Payment & Notes')
                        ->schema([
                            Forms\Components\Select::make('payment_method_id')
                                ->label('Payment Methods')
                                ->relationship('paymentMethod', 'name')
                                ->default(null)
                                ->required(),
                            Forms\Components\Textarea::make('note')
                                // ->columnSpanFull()
                                ->rows(1),                            
                        ])
                    ]),
                Forms\Components\Section::make('Order Products')
                    ->schema([
                        self::getItemsRepeater(),
                    ]),
                Forms\Components\TextInput::make('total_price')
                    ->required()
                    ->readOnly()
                    ->numeric(),
                Forms\Components\TextInput::make('paid_amount')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('change_amount')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater {
        return Repeater::make('orderProducts')
        ->relationship()
        ->live()
        ->afterStateUpdated(function(Forms\Get $get, Forms\Set $set){
            self::updateTotalPrice($get, $set);
        })
        ->schema([
            Forms\Components\Grid::make(10)
            ->schema([
                Forms\Components\Select::make('product_id')
            ->label('Product')
            ->required()
            ->searchable()
            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
            ->options(Product::query()->where('stock', '>', 1)->pluck('name', 'id'))
            ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $product = Product::find($state);
                        $set('unit_price', $product->price ?? 0);
                        $set('stock', $product->stock ?? 0);
                    })
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get){
                $product = Product::find($state);
                $set('unit_price', $product->price ?? 0);
                $set('stock', $product->stock ?? 0);
                $quantity = $get('quantity' ?? 1);
                $stock = $get('stock');

                self::updateTotalPrice($get, $set);
            })
            ->columnSpan([
                'md' => 5,
            ]),
            Forms\Components\TextInput::make('quantity')
            ->required()
            ->numeric()
            ->default(1)
            ->afterStateUpdated(function($state, Forms\Set $set, Forms\Get $get){
                $stock = $get('stock');
                if($state > $stock){
                    $set('quantity', $stock);
                    Notification::make()
                        ->title('Stok tidak cukup')
                        ->warning()
                        ->send();
                }
                
                    self::updateTotalPrice($get, $set);
            })
            ->minValue(1)
            ->columnSpan([
                'md' => 1,
            ]),
            Forms\Components\TextInput::make('stock')
            ->required()
            ->numeric()
            ->readOnly()
            ->columnSpan([
                'md' => 1,
            ]),
            Forms\Components\TextInput::make('unit_price')
            ->required()
            ->numeric()
            ->readOnly()
            ->columnSpan([
                'md' => 3,
            ]),
        ]),
            
        ]);
    
    }
    
    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('orderProducts'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
        $total = $selectedProducts->reduce(function ($total, $product) use ($prices) {
            return $total + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        $set('total_price', $total);
    }

}
