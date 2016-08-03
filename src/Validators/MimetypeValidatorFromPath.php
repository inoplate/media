<?php

namespace Inoplate\Media\Validators;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository; 

class MimetypeValidatorFromPath
{
    /**
     * @var Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystemFactory;

    /**
     * @var Inoplate\Media\Domain\Repositories\Library
     */
    protected $libraryRepostory;

    /**
     * Create new MimetypeValidatorFromPath instance
     * 
     * @param FilesystemFactory $filesystemFactory
     * @param LibraryRepository $libraryRepostory
     */
    public function __construct(FilesystemFactory $filesystemFactory, LibraryRepository $libraryRepostory)
    {
        $this->filesystemFactory = $filesystemFactory;
        $this->libraryRepostory = $libraryRepostory;
    }

    /**
     * Perform rule validation
     * 
     * @param  mixed $attribute
     * @param  mixed $value
     * @param  mixed $parameters
     * @param  mixed $validator
     * 
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        $path = $value;
        $mimes = $parameters;

        $library = $this->libraryRepostory->findByPath($path);

        if(!$library)
            return false;

        $library = $library->toArray();
        $storage = $library['description']['is_moved'] ? $library['description']['storage'] : 'local';
        $mime = $this->filesystemFactory->disk($storage)->mimeType($library['description']['path']);

        return in_array($mime, $mimes);
    }
}