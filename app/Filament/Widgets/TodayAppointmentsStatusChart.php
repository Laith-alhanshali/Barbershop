<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class TodayAppointmentsStatusChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Today Appointments (By Status)';
    }

    protected function getData(): array
    {
        $today = now()->toDateString();

        $counts = Appointment::query()
            ->whereDate('start_at', $today)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $labels = [
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'done'      => 'Done',
            'cancelled' => 'Cancelled',
            'no_show'   => 'No Show',
        ];

        $data = [];
        $finalLabels = [];

        foreach ($labels as $key => $label) {
            $finalLabels[] = $label;
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return [
                'datasets' => [
                    [
                        'label' => 'Appointments',
                        'data'  => $data,
                        'backgroundColor' => [
                            '#f59e0b', // Pending (amber)
                            '#3b82f6', // Confirmed (blue)
                            '#10b981', // Done (green)
                            '#6b7280', // Cancelled (gray)
                            '#ef4444', // No Show (red)
                        ],
                        'borderColor' => '#ffffff',
                        'borderWidth' => 2  ,
                    ],
                ],
                'labels' => $finalLabels,
            ];

    }

    protected function getType(): string
    {
        return 'bar';
    }
}
