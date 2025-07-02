<?php

namespace App\Filament\Widgets;

use App\Enums\RequisitionStatus;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Requisitions', Requisition::query()->where('status', RequisitionStatus::PENDING)->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'), // Yellow/Orange color

            Stat::make(
                    'Value of Pending Requests',
                    'MYR ' . number_format(Requisition::query()->where('status', RequisitionStatus::PENDING)->sum('total_amount'), 2)
                )
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Approved Requisitions', Requisition::query()->where('status', RequisitionStatus::APPROVED)->count())
                ->description('Approved in the last 30 days')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'), // Green color

            Stat::make('Total Products', Product::query()->count())
                ->description('Number of catalog items')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'), // Blue color
            
            Stat::make('Total Vendors', Vendor::query()->count())
                ->description('Registered suppliers')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info'), // Light blue color
        ];
    }
    protected int | string | array $columnSpan = 'full';
}