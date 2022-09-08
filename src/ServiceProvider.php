<?php

namespace Preetender\Uploader;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/uploader.php', 'uploader');
    }

    /**
     * @return void
     */
    public function boot(): void 
    {
        $this->publishes([
            __DIR__ . '/config/uploader.php' => App::configPath('uploader.php')
        ]);
    }
}