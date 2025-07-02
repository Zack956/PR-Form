<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Widgets\RecentRequisitions;
use App\Filament\Widgets\RequisitionsByStatusChart;
use App\Filament\Widgets\StatsOverview;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\MonthlyRequisitionsChart;
use App\Filament\Widgets\TopVendorsChart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors([
            'primary' => '#F59E0B', // A nice amber/yellow color
        ])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->pages([
            Pages\Dashboard::class,
        ])
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        // THIS IS THE NEW, CORRECT PART
        ->widgets([
            StatsOverview::class,
            RecentRequisitions::class,
            RequisitionsByStatusChart::class,
            MonthlyRequisitionsChart::class, 
            TopVendorsChart::class,   
        ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
