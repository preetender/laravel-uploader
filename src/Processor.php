<?php

namespace Preetender\Uploader;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;

class Processor
{
    /**
     * @var array
     */
    protected array $breakpoints = [];

    protected $file;

    protected Image $factory;

    protected string $output;

    protected array $pipes = [];

    /**
     * @var string|null
     */
    protected ?string $directory = null;

    /**
     * @param $file
     * @param string $output
     */
    public function __construct($file, string $output = '/')
    {
        $this->file = $file;
        $this->output = $output;
        $this->breakpoints = Config::get('uploader.sizes');
        $this->setDirectory("{$this->output}/{$this->getHash()}");
    }

    /**
     * @param $file
     * @param string|null $output
     * @param array $sizes
     * @return Processor
     */
    public static function make($file, string $output = null, array $sizes = []): Processor
    {
        $instance = new self($file, $output);
        $instance->breakpoints = $sizes;
        return $instance;
    }

    /**
     * Processaar imagem
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
     * Reverter upload
     *
     * @param int $size
     * @return bool
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

        return Storage::disk(Config::get('uploader.disk', 'local'))->put($filename, $encoded, $mode);
    }

    /**
     * Determinar caminho
     *
     * @param string $dir
     * @return Processor
     */
    public function setDirectory(string $dir): Processor
    {
        $this->directory = trim($dir, '/');

        return $this;
    }

    /**
     * Obter diretório gerado.
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Chave do processo
     *
     * @return string
     */
    public function getHash(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Reverter upload
     *
     * @return void
     */
    public function revert()
    {
        $fs = Container::getInstance()->make('filesystem');

        if ($fs->exists($this->directory)) {
            $fs->deleteDirectory($this->directory);
        }
    }
}