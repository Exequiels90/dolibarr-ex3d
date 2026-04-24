<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Panel Principal';

    protected static ?string $navigationLabel = 'Panel Principal';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    public function getColumns(): int
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ActiveOrdersWidget::class,
            \App\Filament\Widgets\TotalFilamentNeededWidget::class,
            \App\Filament\Widgets\MonthlyNetProfitWidget::class,
            \App\Filament\Widgets\RevenueVsCostsChart::class,
        ];
    }
}
