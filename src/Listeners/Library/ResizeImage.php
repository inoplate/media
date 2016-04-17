<?php

namespace Inoplate\Media\Listeners\Library;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository as Config;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Events\LibraryWasCreated;

class ResizeImage
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @var int|string
     */
    protected $width;

    /**
     * @var int|string
     */
    protected $height;

    /**
     * @var string
     */
    protected $path;

    /**
     * Create new ResizeImage instance
     * 
     * @param Filesystem            $filesystem
     * @param Config                $config
     * @param LibraryRepository     $libraryRepository
     */
    public function __construct(Filesystem $filesystem, Config $config, LibraryRepository $libraryRepository)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->libraryRepository = $libraryRepository;
    }

    /**
     * Handle event
     * 
     * @param  LibraryWasCreated $event
     * @return void
     */
    public function handle(LibraryWasCreated $event)
    {
        $mimes = ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg'];

        $library = $event->library;
        $description = $library->description()->value();

        if(in_array($description['mime'], $mimes)) {
            $file = $this->filesystem->get($description['path']);

            $imageManager = new ImageManager;

            $image = $imageManager->make($file);

            $this->width = $image->width();
            $this->height = $image->height();
            $this->path = $description['path'];

            $large = $this->createLargeSize($image);
            $medium = $this->createMediumSize($image);
            $thumbnail = $this->createThumbnailSize($image);

            $description['large'] = $large;
            $description['medium'] = $medium;
            $description['thumbnail'] = $thumbnail;

            $library->describe( new FoundationDomainModels\Description($description) );

            $this->libraryRepository->save($library);
        }
    }

    /**
     * Resize to thumbnail size
     * 
     * @param  Image  $image
     * @return string
     */
    protected function createThumbnailSize(Image $image)
    {
        $width = $this->config->get('inoplate.media.library.sizes.thumbnail.width', 200);
        $height = $this->config->get('inoplate.media.library.sizes.thumbnail.height', 150);

        return $this->resize($image, $width, $height);
    }

    /**
     * Resize to medium size
     * 
     * @param  Image  $image [description]
     * @return string
     */
    protected function createMediumSize(Image $image)
    {
        $width = $this->config->get('inoplate.media.library.sizes.medium.width', 300);
        $height = $this->config->get('inoplate.media.library.sizes.medium.height', 300);

        return $this->resize($image, $width, $height);
    }

    /**
     * Resize to large size
     * 
     * @param  Image  $image
     * @return string
     */
    protected function createLargeSize(Image $image)
    {
        $width = $this->config->get('inoplate.media.library.sizes.large.width', 1024);
        $height = $this->config->get('inoplate.media.library.sizes.large.height', 1024);

        return $this->resize($image, $width, $height);
    }

    /**
     * Resize image
     * 
     * @param  Image  $image
     * @param  string $width
     * @param  string $height
     * @return string
     */
    protected function resize(Image $image, $width, $height)
    {

        // We won't image resized to larger dimension
        // so we normalize it first
        
        $width = $this->normalizeWidht($width);
        $height = $this->normalizeHeigth($height);

        if($this->config->get('inoplate.media.library.keep_ratio_resize', true)) {
            $image->widen($width);
        } else{
            $image->resize($width, $height);
        }

        $path = $this->generatePath($width.'x'.$height);

        $this->filesystem->put($path, (string) $image->encode());

        return $path;
    }

    /**
     * Normalize width
     * 
     * @param  string $width
     * @return string
     */
    protected function normalizeWidht($width)
    {
        return $this->width <= $width ? $this->width : $width;
    }

    /**
     * Normalize height
     * 
     * @param  string $height
     * @return string
     */
    protected function normalizeHeigth($height)
    {
        return $this->height <= $height ? $this->height : $height;
    }

    /**
     * Generate unique name
     * 
     * @param  string $suffix
     * @return string
     */
    protected function generatePath($suffix)
    {
        // Extract path
        $pathPart = pathinfo($this->path);

        $directory = $pathPart['dirname'];
        $filename = $pathPart['filename'];
        $extension = $pathPart['extension'];

        $fullname = $directory.'/'.$filename.'-'.$suffix.'.'.$extension;

        if($this->filesystem->exists( $fullname )) {
            $fullname = $directory.'/'.$filename.'-'.uniqid().'-'.$suffix.'.'.$extension;
        }

        return $fullname;
    }
}