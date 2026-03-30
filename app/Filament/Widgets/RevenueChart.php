<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Revenue (Last 7 Days)';
    }

    protected function getData(): array
    {
        $start = now()->startOfDay()->subDays(6);
        $end   = now()->endOfDay();

        $rows = Invoice::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('D d');
            $data[]   = (float) ($rows[$date] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data'  => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // change to 'line' if you prefer
    }
}
