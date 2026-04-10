<?php

namespace App\Filament\Widgets;

use App\Models\WorkQueue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalFilamentNeededWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $activeOrders = WorkQueue::active()->with('product.filament')->get();
        
        $totalGrams = $activeOrders->sum(function ($order) {
            return $order->product ? $order->product->total_grams : 0;
        });
        
        $totalKg = $totalGrams / 1000;
        
        // Group by filament type
        $filamentBreakdown = $activeOrders->groupBy(function ($order) {
            return $order->product && $order->product->filament 
                ? $order->product->filament->brand_type . ' - ' . $order->product->filament->color 
                : 'Desconocido';
        })->map(function ($group) {
            return $group->sum(function ($order) {
                return $order->product ? $order->product->total_grams : 0;
            });
        });

        $topFilament = $filamentBreakdown->sortDesc()->first();
        $topFilamentName = $filamentBreakdown->sortDesc()->keys()->first() ?? 'N/A';

        return [
            Stat::make('Filamento Necesario', number_format($totalKg, 2) . ' kg')
                ->description('Para todos los pedidos activos')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary')
                ->chart(array_values($filamentBreakdown->take(10)->toArray())),
            
            Stat::make('Más Usado', $topFilamentName)
                ->description(number_format($topFilament / 1000, 2) . ' kg')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            
            Stat::make('Filamentos Únicos', $filamentBreakdown->count())
                ->description('Tipos diferentes necesarios')
                ->descriptionIcon('heroicon-m-swatch')
                ->color('success'),
        ];
    }
}
