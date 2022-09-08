<?php

namespace Preetender\Uploader\Concerns;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;

trait HasFile
{
    /**
     * Retrieve list of sizes
     *
     * @return object
     */
    public function getImagesAttribute()
    {
        $image_key = $this->getImageKey();

        if (!isset($this->attributes[$image_key]) || !$this->attributes[$image_key]) {
            return [];
        }

        $prepareImages = function () use ($image_key) {
            $fs = Container::getInstance()->make('filesystem')->disk(
                Config::get('uploader.disk', 'local')
            );

            $images = [];

            foreach ($this->getSizes() as $size) {
                $link = sprintf("%s/%s.webp", $this->attributes[$image_key], $size);

                if (!$fs->exists($link)) {
                    continue;
                }

                $images[$size] = $fs->url($link);
            }

            return (object) $images;
        };

        if (!Config::get('uploader.cache.enable')) {
            return $prepareImages();
        }

        return Container::getInstance()
            ->make('cache')
            ->driver(Config::get('uploader.cache.driver', 'file'))
            ->rememberForever($this->getCacheKey(), $prepareImages);
    }

    /**
     * Remove cache image.
     *
     * @return void
     */
    public function cleanCacheImages(): void
    {
        if (!Config::get('uploader.cache.enable')) {
            return;
        }

        $cache_key = $this->getCacheKey();

        $keys = [
            "$cache_key.images",
            "$cache_key.main",
            "$cache_key.avatar"
        ];

        foreach ($keys as $key) {
            Container::getInstance()->make('cache')->forget($key);
        }
    }

    /**
     * Cache key
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        $prefix = Config::get('uploader.cache.prefix');

        return sprintf("%s{$this->getTable()}.{$this->id}", $prefix);
    }

    /**
     * Field related of image.
     *
     * @return string
     */
    public function getImageKey()
    {
        return 'image';
    }
}
