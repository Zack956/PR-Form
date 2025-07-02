<?php

namespace App\Filament\Widgets;

use App\Models\Requisition;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RequisitionsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Requisitions by Status';
    protected static ?int $sort = 3; // Order on dashboard

    protected function getData(): array
    {
        // Query the data
        $data = Requisition::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Requisitions',
                    // Use the data we queried
                    'data' => array_values($data),
                    // Set colors for the chart slices
                    'backgroundColor' => [
                        '#F59E0B', // Pending (Warning/Amber)
                        '#10B981', // Approved (Success/Green)
                        '#EF4444', // Rejected (Danger/Red)
                    ],
                ],
            ],
            // Use the status names as labels
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Can also be 'bar', 'line', etc.
    }
}