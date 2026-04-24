<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Illuminate\Routing\Middleware\SubstituteBindings;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(false)
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])
            ->brandName('Ex3D Production Management')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            ->collapsedSidebarWidth('4rem')
            ->navigationGroups([
                'Gestión de Producción',
                'Pedidos y Cola',
                'Sistema',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament')
            ->discoverPages(in: app_path('Filament/Pages'))
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->darkMode(true) // Force dark mode as requested
            ->renderHook(
                'panels::head.end',
                fn (): string => '<style>
                    :root {
                        --color-gray-50: 17 24 39;
                        --color-gray-100: 31 41 55;
                        --color-gray-200: 55 65 81;
                        --color-gray-300: 75 85 99;
                        --color-gray-400: 107 114 128;
                        --color-gray-500: 156 163 175;
                        --color-gray-600: 209 213 219;
                        --color-gray-700: 229 231 235;
                        --color-gray-800: 243 244 246;
                        --color-gray-900: 249 250 251;
                    }
                </style>',
            );
    }
}
