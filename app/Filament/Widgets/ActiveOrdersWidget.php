<?php

namespace App\Filament\Widgets;

use App\Models\WorkQueue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pendingOrders = WorkQueue::where('status', WorkQueue::STATUS_PENDING)->count();
        $inPrinterOrders = WorkQueue::where('status', WorkQueue::STATUS_IN_PRINTER)->count();
        $finishedOrders = WorkQueue::where('status', WorkQueue::STATUS_FINISHED)->count();
        $totalActive = $pendingOrders + $inPrinterOrders + $finishedOrders;

        return [
            Stat::make('Pedidos Activos', $totalActive)
                ->description('Pedidos en progreso')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('primary')
                ->chart([$pendingOrders, $inPrinterOrders, $finishedOrders]),
            
            Stat::make('Pendientes', $pendingOrders)
                ->description('Esperando producción')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('En Impresora', $inPrinterOrders)
                ->description('Imprimiendo actualmente')
                ->descriptionIcon('heroicon-m-cube-transparent')
                ->color('info'),
            
            Stat::make('Terminados', $finishedOrders)
                ->description('Listos para entrega')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
