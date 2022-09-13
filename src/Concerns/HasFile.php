<?php

namespace Preetender\Uploader\Concerns;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Preetender\Uploader\Models\Gallery;
use Preetender\Uploader\Processor;
use stdClass;

trait HasFile
{
    /**
     * Retrieve list of sizes
     *
     * @return object
     */
    public function getImagesAttribute()
    {
        $processFiles = function () {
            $images = [];

            foreach ($this->galleries as $gallery) {
                $breakpoints = new stdClass;

                foreach ($gallery->files as $file) {
                    $breakpoints->{$file->width} = Processor::disk($gallery->disk)->url(
                        sprintf('%s/%s', $gallery->folder, $file->filename)
                    );
                }

                $images[] = $breakpoints;
            }

            return $images;
        };

        if (!Config::get('uploader.cache.enable')) {
            return $processFiles();
        }

        Request::has('expired') && $this->cleanCacheImages();

        return Processor::cache()->rememberForever($this->getCacheKey(), $processFiles);
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
                "$cache_key.avatar",
                $cache_key
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
