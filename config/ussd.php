<?php

return [
    'menu-path' => env('USSD_MENU_PATH', 'menus'),

    'cache' => [
        'driver' => env('USSD_CACHE_DRIVER', 'file'),
        'ttl' => env('USSD_CACHE_TTL', 120),
    ],

    'tag-ns' => [
        'Bmatovu\\Ussd\\Tags',
        'App\\Ussd\\Tags',
    ],

    'action-ns' => [
        'App\\Ussd\\Actions',
        'Bmatovu\\Ussd\\Actions',
    ],

    'provider-ns' => [
        'App\\Ussd\\Providers',
        'Bmatovu\\Ussd\\Providers',
    ],
];
