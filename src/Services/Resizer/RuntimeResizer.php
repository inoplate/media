<?php 

namespace Inoplate\Media\Services\Resizer;

use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageCache;

class RuntimeResizer extends BaseResizer implements Resizer
{
    /**
     * @var LibraryRepository
     */
    protected $libraryRepostiory;

    /**
     * @var FilesystemFactory
     */
    protected $filesystemFactory;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * Create new RuntimeResizer instance
     * 
     * @param LibraryRepository $libraryRepostiory
     * @param FilesystemFactory $filesystemFactory
     * @param ImageManager      $imageManager
     */
    public function __construct(
        LibraryRepository $libraryRepostiory,
        FilesystemFactory $filesystemFactory,
        ImageManager $imageManager
    ) {
        $this->libraryRepostiory = $libraryRepostiory;
        $this->filesystemFactory = $filesystemFactory;
        $this->imageManager = $imageManager;
    }

    /**
     * Apply resize
     * 
     * @param string $path
     * @return Psr-7 stream
     */
    public function apply($path)
    {
        $library = $this->libraryRepostiory->findByPath($path)->toArray();
        $storage = $library['description']['is_moved'] ? $library['description']['storage'] : 'local';

        $filesystem = $this->filesystemFactory->disk($storage);

        $stream = $filesystem->getDriver()->readStream($path);
        $lastModified = $filesystem->lastModified($path);

        return $this->imageManager->cache(function($image) use ($stream, $lastModified){

            $this->process($image->setProperty('lastModified', $lastModified)->make($stream));

        }, $this->getLifetime());
    }

    /**
     * Process image resizing
     * 
     * @param  Image  $image
     * @return Image
     */
    protected function process(ImageCache $image)
    {
        switch ($this->getMode()) {
            case 'widen':
                return $image->widen($this->width);
                break;

            case 'heighten':
                return $image->heighten($this->height);
                break;

            case 'fit':
                return $image->fit($this->width, $this->height);
                break;
            
            default:
                return $image->resize($this->width, $this->height);
                break;
        }
    }
}