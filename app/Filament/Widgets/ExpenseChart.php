<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran';
    protected static ?int $sort = 2;
    public ?string $filter = 'today';
    protected static string $color = 'danger';

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $dateRange = match($activeFilter){
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'period' => 'perHour'
            ],

            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'period' => 'perDay'
            ],

            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'period' => 'perDay'
            ],

            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'period' => 'perMonth'
            ],
        };

        $query = Trend::model(Expense::class)
        ->between(
            start: $dateRange['start'],
            end: $dateRange['end'],
        );

        if($dateRange['period'] === 'perHour'){
            $data = $query->perHour();
        }elseif($dateRange['period'] === 'perDay'){
            $data = $query->perDay();
        }else{
            $data = $query->perMonth();
        }
        $data = $data->sum('amount');

        $labels = $data->map(function(TrendValue $value) use ($dateRange){
            $date = Carbon::parse($value->date);
            if($dateRange['period'] === 'perHour'){
                return $date->format('H:i');
            }elseif($dateRange['period'] === 'perDay'){
                return $date->format('d M');
            }
            return $date->format('M Y');
        });

    return [
        'datasets' => [
            [
                'label' => 'Expense '.$this->getFilters()[$activeFilter],
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $labels,
    ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'color' => '#dc2626', // warna label merah (Tailwind danger-600)
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderColor' => '#dc2626', // warna garis merah
                    'backgroundColor' => 'rgba(220,38,38,0.2)', // merah transparan
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'color' => '#dc2626',
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'color' => '#dc2626',
                    ],
                ],
            ],
        ];
    }

}
