<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenuePeriodChart extends ChartWidget
{
    // فلتر افتراضي عند فتح الداشبورد
    public ?string $filter = 'week';

    public function getHeading(): ?string
    {
        return 'Revenue (Day / Week / Month)';
    }

    protected function getFilters(): ?array
    {
        return [
            'day'   => 'Today (Hourly)',
            'week'  => 'Last 7 Days',
            'month' => 'This Month',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'week';

        if ($filter === 'day') {
            $start = now()->startOfDay();
            $end   = now()->endOfDay();

            $rows = Invoice::query()
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$start, $end])
                ->selectRaw('HOUR(paid_at) as h, SUM(total) as total')
                ->groupBy('h')
                ->pluck('total', 'h');

            $labels = [];
            $data   = [];

            for ($h = 0; $h <= 23; $h++) {
                $labels[] = str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00';
                $data[]   = (float) ($rows[$h] ?? 0);
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

        if ($filter === 'month') {
            $start = now()->startOfMonth()->startOfDay();
            $end   = now()->endOfDay();

            $rows = Invoice::query()
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$start, $end])
                ->selectRaw('DATE(paid_at) as d, SUM(total) as total')
                ->groupBy('d')
                ->pluck('total', 'd');

            $labels = [];
            $data   = [];

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dateKey = $cursor->toDateString();
                $labels[] = $cursor->format('d M');
                $data[]   = (float) ($rows[$dateKey] ?? 0);

                $cursor->addDay();
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

        // week (default): آخر 7 أيام
        $start = now()->startOfDay()->subDays(6);
        $end   = now()->endOfDay();

        $rows = Invoice::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as d, SUM(total) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

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
        return 'bar'; // تقدر تغيرها لـ 'bar' لو تفضّل أعمدة
    }
}
