<?php

return [
    'name' => env('APP_NAME', 'Bistro Suite API'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'admin_demo_read_only' => (bool) env('ADMIN_DEMO_READ_ONLY', false),
    'admin_demo_read_only_message' => env('ADMIN_DEMO_READ_ONLY_MESSAGE', 'Modo demo: no está permitido modificar, agregar ni quitar información.'),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Bogota',
    'locale' => env('APP_LOCALE', 'es'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'es_CO'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'previous_keys' => array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
