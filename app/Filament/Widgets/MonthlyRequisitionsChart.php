<?php

namespace App\Filament\Widgets;

use App\Models\Requisition;
use App\Enums\RequisitionStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyRequisitionsChart extends ChartWidget
{
    protected static ?string $heading = 'Approved Requisition Value (Last 12 Months)';
    protected static ?int $sort = 4; // To place it after the pie chart

    protected function getData(): array
    {
        // Get the total_amount of APPROVED requisitions, grouped by month
        $data = Requisition::query()
            ->where('status', RequisitionStatus::APPROVED)
            ->select(
                DB::raw('SUM(total_amount) as total_value'),
                DB::raw("DATE_FORMAT(approved_at, '%Y-%m') as month")
            )
            ->whereYear('approved_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_value', 'month')
            ->all();

        // Prepare the labels and data for the chart
        $labels = [];
        $values = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = now()->month($i)->format('Y-m');
            $labels[] = now()->month($i)->format('M'); // e.g., 'Jan', 'Feb'
            $values[] = $data[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Requisition Value (MYR)',
                    'data' => $values,
                    'borderColor' => '#10B981', // Green color
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
