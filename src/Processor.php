<?php

namespace Preetender\Uploader;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Preetender\Uploader\Models\Gallery;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class Processor
{
    protected mixed $file;

    protected Image $factory;

    protected string $output;

    protected array $pipes = [];

    protected array $breakpoints = [];

    protected Gallery $gallery;

    /**
     * @var string|null
     */
    protected ?string $directory = null;

    /**
     * @param $file
     * @param string $output
     */
    public function __construct($file, string $output = '/', array $sizes = [])
    {
        $this->file = $file;
        $this->output = $output ?? '/';
        $this->breakpoints = $sizes ?? Config::get('uploader.sizes');
        $this->initialize();
    }

    /**
     * @return void
     */
    private function initialize(): void
    {
        $this->setDirectory("{$this->output}/{$this->getHash()}");

        $this->gallery = Gallery::create([
            'folder' => $this->getDirectory(),
            'disk' => Config::get('uploader.disk', 'local')
        ]);
    }

    /**
     * @param $file
     * @param string|null $output
     * @param array $sizes
     * @return Processor
     */
    public static function make($file, string $output = null, array $sizes = []): Processor
    {
        return new self($file, $output, $sizes);
    }

    /**
     * Enqueue processes.
     *
     * @return array
     */
    public function process(): array
    {
        foreach ($this->breakpoints as $size) {
            $upload = $this->pipe($size);

            if (!$upload) {
                unset($this->pipes[$size]);
            }
        }

        return $this->pipes;
    }

    /**
     * Process upload.
     *
     * @param int $width
     * @param int $height
     * @param string $mode
     * @return mixed
     */
    public function pipe(int $width, ?int $height = null, string $mode = 'public')
    {
        $image = Image::make($this->file);

        if ($width > $image->width()) {
            return false;
        }

        $extension = Config::get('uploader.compress.extension', 'webp');

        $make = $image->resize($width, $height, fn ($h) => $h->aspectRatio());

        $filename = Str::of("{$this->getDirectory()}/:width.:extension")
            ->replace('-', '')
            ->replace([':width', ':extension'], [$width, $extension])
            ->__toString();

        $this->pipes[$width] = $filename;

        $encoded = $make->encode(
            $extension,
            Config::get('uploader.compress.quality', 85)
        )->encoded;

        $saved = $this->disk()->put($filename, $encoded, $mode);

        throw_if(!$saved, new UploadException('file_not_saved'));

        $galleryFile = $this->gallery->files()->create([
            'filename' => $filename,
            'extension' => $make->extension,
            'size' => $make->filesize(),
            'width' => $make->width(),
            'height' => $make->height()
        ]);

        return $galleryFile;
    }

    /**
     * @param string $dir
     * @return Processor
     */
    public function setDirectory(string $dir): Processor
    {
        $this->directory = trim($dir, '/');

        return $this;
    }

    /**
     * Get directory.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Get unique key.
     *
     * @return string
     */
    public function getHash($size = 16): string
    {
        return Str::random($size);
    }

    /**
     * Result upload.
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->pipes;
    }

    /**
     * @return Gallery
     */
    public function getGallery(): Gallery
    {
        return $this->gallery;
    }

    /**
     * Reverter upload
     *
     * @return void
     */
    public function revert()
    {
        $fs = $this->disk();

        if ($fs->exists($this->directory)) {
            $fs->deleteDirectory($this->directory);
        }

        $this->getGallery()->delete();
    }

    /**
     * Get disk in use.
     *
     * @return Storage
     */
    public static function disk($disk = null)
    {
        return Storage::disk($disk ?? Config::get('uploader.disk', 'local'));
    }

    /**
     * Get cache in use.
     *
     * @return Cache
     */
    public static function cache()
    {
        return Container::getInstance()
            ->make('cache')
            ->driver(Config::get('uploader.cache.driver', 'file'));
    }
}
