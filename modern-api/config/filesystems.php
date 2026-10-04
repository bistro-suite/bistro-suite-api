<?php

$publicStoragePath = env('APP_PUBLIC_STORAGE_PATH', storage_path('app/public'));

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local', 'root' => storage_path('app/private'), 'serve' => true,
            'throw' => false, 'report' => false,
        ],
        'public' => [
            'driver' => 'local', 'root' => $publicStoragePath,
            'url' => env('APP_URL').'/storage', 'visibility' => 'public',
            'throw' => false, 'report' => false,
        ],
    ],
    'links' => [public_path('storage') => $publicStoragePath],
];
