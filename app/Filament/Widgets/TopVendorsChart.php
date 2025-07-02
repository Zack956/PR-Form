<?php

namespace App\Filament\Widgets;

use App\Models\Requisition;
use App\Enums\RequisitionStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopVendorsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Vendors (by Requisition Count)';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        // Get the count of requisitions per vendor, for APPROVED requisitions only
        $data = Requisition::query()
            ->where('status', RequisitionStatus::APPROVED)
            ->join('vendors', 'requisitions.vendor_id', '=', 'vendors.id')
            ->select('vendors.name as vendor_name', DB::raw('count(*) as count'))
            ->groupBy('vendors.name')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'vendor_name')
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Number of Requisitions',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#3B82F6', '#8B5CF6', '#F97316', '#14B8A6', '#EC4899',
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}