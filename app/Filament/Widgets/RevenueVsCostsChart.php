<?php

namespace App\Filament\Widgets;

use App\Models\WorkQueue;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class RevenueVsCostsChart extends ChartWidget
{
    protected static ?string $heading = 'Ingresos vs Costos Reales (Últimos 30 Días)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $last30Days = now()->subDays(29)->startOfDay();
        $data = $this->getDailyRevenueAndCosts($last30Days);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data['revenue'],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Production Costs',
                    'data' => $data['costs'],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Net Profit',
                    'data' => $data['profit'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getDailyRevenueAndCosts($startDate): array
    {
        $endDate = now()->endOfDay();
        $labels = [];
        $revenue = [];
        $costs = [];
        $profit = [];

        // Use delivered_at field instead of created_at for real sales performance
        $orders = WorkQueue::whereBetween('delivered_at', [$startDate, $endDate])
            ->where('status', WorkQueue::STATUS_DELIVERED)
            ->whereNotNull('delivered_at')
            ->with('product')
            ->get()
            ->groupBy(function ($order) {
                return $order->delivered_at->format('M j');
            });

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateLabel = $date->format('M j');
            $labels[] = $dateLabel;

            $dayOrders = $orders->get($dateLabel, collect());
            
            $dayRevenue = $dayOrders->sum('agreed_price');
            $dayCosts = $dayOrders->sum(function ($order) {
                return $order->product ? $order->product->calculateTotalProductionCost() : 0;
            });
            $dayProfit = $dayRevenue - $dayCosts;

            $revenue[] = round($dayRevenue, 2);
            $costs[] = round($dayCosts, 2);
            $profit[] = round($dayProfit, 2);
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'costs' => $costs,
            'profit' => $profit,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "$" + value.toFixed(0);
                        }',
                    ],
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.1)',
                    ],
                    'ticks' => [
                        'color' => 'rgba(255, 255, 255, 0.7)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.1)',
                    ],
                    'ticks' => [
                        'color' => 'rgba(255, 255, 255, 0.7)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'color' => 'rgba(255, 255, 255, 0.7)',
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            label += "$" + context.parsed.y.toFixed(2);
                            return label;
                        }',
                    ],
                ],
            ],
        ];
    }
}
