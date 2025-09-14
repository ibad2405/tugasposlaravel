<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LastOrder extends BaseWidget
{
     protected int|string|array $columnSpan = 'full';

     protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->paginated(false)
            ->columns([

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Tanggal'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone'),

                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Payment Method'),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('idr') // kalau mau format uang
                    ->label('Total'),

            ]);
    }
}
