<?php

return [
    'disk' => env('UPLOADER_DISK', 'local'),

    'cache' => [
        'enable' => env('UPLOADER_CACHE_ENABLE', false),
        'driver' => env('UPLOADER_CACHE_DRIVE', 'file'),
        'prefix' => env('UPLOADER_CACHE_PREFIX', 'uploader_')
    ],

    'compress' => [
        'extension' => env('UPLOADER_IMAGE_EXTENSION', 'webp'),
        'quality' => env('UPLOADER_IMAGE_QUALITY', 85)
    ],

    'sizes' => [
        25,
        100,
        320,
        414,
        667,
        736,
        768,
        1024,
        1920
    ]
];
