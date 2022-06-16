<?php

return [
    'menus-path' => env('USSD_MENUS_PATH', 'menus'),

    'cache' => [
        'driver' => env('USSD_CACHE_DRIVER', 'file'),
        'ttl' => env('USSD_CACHE_TTL', 120),
    ],

    'tag-ns' => [
        'Bmatovu\\Ussd\\Tags',
        'App\\Ussd\\Tags',
    ],

    'action-ns' => [
        'Bmatovu\\Ussd\\Actions',
        'App\\Ussd\\Actions',
    ],

    'provider-ns' => [
        'Bmatovu\\Ussd\\Providers',
        'App\\Ussd\\Providers',
    ],
];
