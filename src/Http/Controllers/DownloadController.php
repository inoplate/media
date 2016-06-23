<?php

namespace Inoplate\Media\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Roseffendi\Authis\Authis;
use Inoplate\Media\Services\Renderer\Renderer;
use Inoplate\Media\Services\Resizer\Resizer;
use Inoplate\Foundation\Http\Controllers\Controller;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Exceptions\SizeNotFoundException;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var LibraryRepository
     */
    protected $libraryRepository;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var Authis
     */
    protected $authis;

    /**
     * @var Resizer
     */
    protected $resizer;

    /**
     * @var Illuminate\Contracts\Filesystem\Factory
     */
    protected $filesystemFactory;

    /**
     * @param Renderer          $renderer
     * @param LibraryRepository $libraryRepository
     * @param Guard             $auth
     * @param Authis            $authis
     * @param Resizer           $resizer
     * @param FilesystemFactory $filesystemFactory
     */
    public function __construct(
        Renderer $renderer, 
        LibraryRepository $libraryRepository, 
        Guard $auth, 
        Authis $authis,
        Resizer $resizer,
        FilesystemFactory $filesystemFactory
    ) {
        $this->renderer = $renderer;
        $this->libraryRepository = $libraryRepository;
        $this->auth = $auth;
        $this->authis = $authis;
        $this->resizer = $resizer;
        $this->filesystemFactory = $filesystemFactory;
    }

    /**
     * Render file
     * 
     * @param  Request  $request
     * @param  string   $path
     * @param  string   $dimension
     * @return response
     */
    public function getRender(Request $request, $path, $dimension = null)
    {
        $library = $this->libraryRepository->findByPath($path);

        if(is_null($library))
            abort(404); // No library was found
        else
            $library = $library->toArray();

        $this->authorizeDownload($request, $library);
        $this->validateEtag($request, $library);

        $headers = $this->prepareHeaders($library, $dimension);
        $content = $this->getFileContent($library, $dimension);

        return $this->renderer->setHeaders($headers)
                              ->render($content);
    }

    /**
     * Download file
     * @param  Request  $request
     * @param  string   $path
     * @return response
     */
    public function getDownload(Request $request, $path)
    {
        $library = $this->libraryRepository->findByPath($path);

        if(is_null($library))
            abort(404); // No library was found
        else
            $library = $library->toArray();

        $this->authorizeDownload($request, $library);
        $this->validateEtag($request, $library);
        
        $headers = $this->prepareHeaders($library);
        $headers['Content-Disposition'] = 'attachment; filename="'.$library['description']['name'].'"';

        $content = $this->getFileContent($library);
        return $this->renderer->setHeaders($headers)
                              ->render($content);
    }

    /**
     * Preparing response header
     * @param  array $library
     * @return array
     */
    protected function prepareHeaders($library, $dimension = null)
    {
        $filesystem = $this->getFilesystem($library);

        $headers['Content-Type'] = $filesystem->mimeType($library['description']['path']);
        $headers['Cache-Control'] = 'max-age='.config('inoplate.media.library.cache_lifetime', 150).', public';
        $headers['Last-Modified'] = gmdate('r', $filesystem->lastModified($library['description']['path']));
        $headers['ETag'] = $this->getEtag($filesystem, $library);

        if((is_null($dimension)) && (!is_image($headers['Cache-Control']))){
            $headers['Content-Length'] = $filesystem->size($library['description']['path']);
        }

        return $headers;
    }

    /**
     * Getting file content
     * @param  array $library
     * @param  array $dimension
     * @return mixed
     */
    protected function getFileContent($library, $dimension = null)
    {
        $filesystem = $this->getFilesystem($library);
        $mimeType = $filesystem->mimeType($library['description']['path']);

        if((is_image($mimeType)) && ( !is_null($dimension))) {
            $dimension = config('inoplate.media.library.sizes.'.$dimension);
            
            if(!$dimension) { 
                return response('Bad request', 400); // Bad request, no dimension is found
            }

            $mode = isset($dimension['mode']) ? $dimension['mode'] : config('inoplate.media.library.resize_mode');
            $lifetime = config('inoplate.media.library.cache_lifetime');

            return $this->resizer->setMode($mode)
                                 ->setDimension($dimension['width'], $dimension['height'])
                                 ->setLifetime($lifetime)
                                 ->apply($library['description']['path']);
        } else {
            return $filesystem->getDriver()->readStream($library['description']['path']);;
        }
    }

    /**
     * Retrieve filesystem
     * @param  array $library
     * @return Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getFilesystem($library)
    {
        $storage = $library['description']['is_moved'] ? $library['description']['storage'] : 'local';

        return $this->filesystemFactory->disk($storage);
    }

    /**
     * Authorize download
     * @param  array $library
     * @return void
     */
    protected function authorizeDownload(Request $request, $library)
    {
        $user = $request->user();

        if(is_null($user)) {
            if($library['description']['visibility'] == 'private') {
                abort(403); // User is not authorized to access media library
            }
        } else {
            if(
                ( $library['owner']['id'] != $user->id ) && 
                ( array_search($user->id, array_column($library['sharedTo'], 'id')) === false) && 
                ( $library['description']['visibility'] == 'private') &&
                ( !$this->authis->check('media.admin.libraries.view.all'))
            ) {
                abort(403); // User is not authorized to download file
            }
        }
    }

    protected function validateEtag(Request $request, $library)
    {
        $filesystem = $this->getFilesystem($library);
        $etag = $request->getEtags();

        if(isset($etag[0])) {
            $etag = str_replace('"', '', $etag[0]);

            if ( $etag === $this->getEtag($filesystem, $library) ) {
                abort(304);
            }
        }
    }

    /**
     * Generate etag
     * 
     * @param  array $library
     * @return string
     */
    protected function getEtag($filesystem, $library)
    {
        return md5($library['description']['path'].$filesystem->lastModified($library['description']['path']));
    }
}