<?php

namespace Preetender\Uploader\Concerns;

use Illuminate\Support\Facades\Config;
use Preetender\Uploader\Models\Gallery;
use Preetender\Uploader\Processor;

trait HasFile
{
    /**
     * Retrieve list of sizes
     *
     * @return object
     */
    public function getImagesAttribute()
    {
        $folder = $this->getImageKey();

        if (!isset($this->attributes[$folder]) || !$this->attributes[$folder]) {
            return [];
        }

        $images = $this->galleries->map(
            fn ($gallery) => $gallery->files->map(
                fn ($file) => Processor::disk($gallery->disk)->url(
                    sprintf('%s/%s.%s', $gallery->folder, $file->filename, $file->extension)
                )
            )
        );

        if (!Config::get('uploader.cache.enable')) {
            return $images;
        }

        return Processor::cache()->rememberForever($this->getCacheKey(), fn () => $images);
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

        collect(
            [
                "$cache_key.images",
                "$cache_key.main",
                "$cache_key.avatar"
            ]
        )->each(fn ($key) => Processor::cache()->forget($key));
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
     * @return mixed
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'origin');
    }
}
