<?php

namespace App\Filament\Widgets;

use App\Models\WorkQueue;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyNetProfitWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Ganancia Neta Mensual';

    protected function getStats(): array
    {
        $currentMonth = now()->startOfMonth();
        $currentMonthProfit = $this->calculateMonthlyProfit($currentMonth);
        $lastMonthProfit = $this->calculateMonthlyProfit($currentMonth->copy()->subMonth());
        
        $profitChange = $lastMonthProfit > 0 ? (($currentMonthProfit - $lastMonthProfit) / $lastMonthProfit) * 100 : 0;
        $profitChangeColor = $profitChange >= 0 ? 'success' : 'danger';
        $profitChangeIcon = $profitChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        return [
            Stat::make('Monthly Net Profit', '$' . number_format($currentMonthProfit, 2))
                ->description($profitChange >= 0 ? 'Increased by ' . abs(round($profitChange, 1)) . '%' : 'Decreased by ' . abs(round($profitChange, 1)) . '%')
                ->descriptionIcon($profitChangeIcon)
                ->color($profitChangeColor)
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Sample data - would be calculated from actual monthly data
        ];
    }

    private function calculateMonthlyProfit($monthStart): float
    {
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        return WorkQueue::whereBetween('delivered_at', [$monthStart, $monthEnd])
            ->where('status', WorkQueue::STATUS_DELIVERED)
            ->whereNotNull('delivered_at')
            ->get()
            ->sum(function ($order) {
                if (!$order->product) {
                    return 0;
                }
                return $order->agreed_price - $order->product->calculateTotalProductionCost();
            });
    }
}
