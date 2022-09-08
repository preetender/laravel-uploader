<?php

return [
    'disk' => 'local',

    'cache' => [
        'enable' => true,
        'driver' => 'file',
        'prefix' => 'uploader_'
    ],

    'compress' => [
        'extension' => 'webp',
        'quality' => 100
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
