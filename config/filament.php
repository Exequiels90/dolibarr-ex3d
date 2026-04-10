<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Panel
    |--------------------------------------------------------------------------
    |
    | This option controls the default panel that will be used when no panel
    | is explicitly specified. This should be the ID of a panel that is
    | defined in the "panels" configuration array below.
    |
    */

    'default' => env('FILAMENT_DEFAULT_PANEL', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    |
    | Here you may define the panels that will be available in your app. The
    | panel configuration is used to determine how the user will interact
    | with Filament. Each panel has its own configuration and resources.
    |
    */

    'panels' => [

        'admin' => [
            'id' => 'admin',
            'path' => env('FILAMENT_ADMIN_PATH', 'admin'),
            'login' => true,
            'registration' => false,
            'password_reset' => true,
            'email_verification' => false,
            'profile' => true,
            'colors' => [
                'primary' => '#6366f1',
            ],
            'brand' => [
                'name' => env('APP_NAME', 'Ex3D Production Management'),
                'logo' => env('FILAMENT_BRAND_LOGO'),
                'logoHeight' => '2rem',
            ],
            'middleware' => [],
            'auth_guard' => null,
            'auth_middleware' => [],
            'global_search_key_bindings' => ['command+k', 'ctrl+k'],
            'navigation_groups' => [
                'Production Management',
                'Orders & Queue',
                'System',
            ],
            'sidebar' => [
                'width' => '20rem',
                'collapsed_width' => '4rem',
                'groups' => [
                    'collapsible' => true,
                ],
            ],
            'vite' => [
                'theme' => null,
                'builds' => [
                    'resources/css/app.css' => 'resources/css/app.css',
                    'resources/js/app.js' => 'resources/js/app.js',
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Registration
    |--------------------------------------------------------------------------
    |
    | Here you may configure tenant registration settings for your app. The
    | tenant registration configuration is used to determine how tenants are
    | registered in your app.
    |
    */

    'tenant_registration' => [
        'enabled' => false,
        'model' => null,
        'database' => null,
        'prefix' => null,
        'suffix' => null,
        'middleware' => [],
        'routes' => false,
        'home_url' => null,
        'brand' => [
            'name' => env('APP_NAME'),
            'logo' => null,
            'logoHeight' => '2rem',
        ],
        'profile' => true,
        'avatar' => true,
        'auth_guard' => null,
        'auth_middleware' => [],
        'global_search_key_bindings' => ['command+k', 'ctrl+k'],
        'sidebar' => [
            'width' => '20rem',
            'collapsed_width' => '4rem',
            'groups' => [
                'collapsible' => true,
            ],
        ],
        'vite' => [
            'theme' => null,
            'builds' => [
                'resources/css/app.css' => 'resources/css/app.css',
                'resources/js/app.js' => 'resources/js/app.js',
            ],
        ],
    ],

];
