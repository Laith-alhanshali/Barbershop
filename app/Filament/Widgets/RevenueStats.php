<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todayRevenue = Invoice::query()
            ->where('status', 'paid')
            ->whereDate('paid_at', now()->toDateString())
            ->sum('total');

        $weekRevenue = Invoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfDay()->subDays(6), now()->endOfDay()])
            ->sum('total');

        $unpaidCount = Invoice::query()
            ->where('status', 'unpaid')
            ->count();

        return [
            Stat::make(__('Today Revenue'), number_format((float) $todayRevenue, 2)),
            Stat::make(__('Last 7 Days Revenue'), number_format((float) $weekRevenue, 2)),
            Stat::make(__('Unpaid Invoices'), $unpaidCount),
        ];
    }
}
