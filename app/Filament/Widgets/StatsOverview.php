<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Order;
use App\Models\Expense;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $products = Product::count();
        $orders = Order::count();
        $omset = Order::sum('total_price');
        $pengeluaran = Expense::sum('amount');
        return [
            Stat::make('Produk', $products),
            Stat::make('Order', $orders),
            Stat::make('Omset', number_format($omset,0,",",".")),
            Stat::make('Pengeluaran', number_format($pengeluaran,0,",",".")),
        ];
    }
}
