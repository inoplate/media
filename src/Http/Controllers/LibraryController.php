<?php

namespace Inoplate\Media\Http\Controllers;

use Inoplate\Media\Services\Uploader\Receiver;
use Inoplate\Foundation\Http\Controllers\Controller;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Commands;
use Inoplate\Foundation\App\Services\Bus\Dispatcher as Bus;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class LibraryController extends Controller
{
    protected $libraryRepository;

    protected $filesystemFactory;

    public function __construct(LibraryRepository $libraryRepository, FilesystemFactory $filesystemFactory)
    {
        $this->libraryRepository = $libraryRepository;
        $this->filesystemFactory = $filesystemFactory;
    }

    public function getIndex(Request $request)
    {
        $page = $request->input('page') ?: 1;
        $search = $request->input('search');

        $perPage = config('inoplate.media.library.per_page', 10);
        $data = $this->libraryRepository->get($page, $search);

        $paginator = new Paginator($data, $perPage, $page);

        return $this->getResponse('inoplate-media::library.index', ['libraries' => $paginator->toArray()]);
    }

    public function upload(Request $request, Bus $bus, Receiver $receiver)
    {
        return $receiver->receive(function($destination) use ($request, $bus){
            $name = $request->input('flowFilename');

            $description = [
                'name' => $name,
                'path' => $destination,
                'title' => $name,
                'alt' => $name,
                'caption' => $name,
                'description' => $name,
                'storage' => config('filesystems.default'),
                'visibility' => config('inoplate.media.library.default_visibility', 'public'),
                'is_moved' => false
            ];

            $userId = $request->user()->id;

            $bus->dispatch(new Commands\CreateNewLibrary($userId, $description));

            $uploaded = $this->libraryRepository->findByPath($destination);

            return $this->formSuccess(
                route('media.admin.libraries.update.get', ['id' => $uploaded->id()->value()]), 
                [
                    'message' => trans('inoplate-media::messages.library.created'), 
                    'library' => $this->generateReturnedData($uploaded->toArray())
                ]
            );
        });
    }

    /**
     * Generate returned data
     * 
     * @param  array $library
     * @return array
     */
    protected function generateReturnedData($library)
    {
        if($library['description']['is_moved']) {
            $filesystem = $this->filesystemFactory->disk($library['description']['storage']);
        }else {
            $filesystem = $this->filesystemFactory->disk('local');
        }

        $library['description']['mime'] = $filesystem->mimeType($library['description']['path']);
        $library['description']['size'] = $filesystem->size($library['description']['path']);
        
        if(is_image($library['description']['mime'])) {
            $library['description']['thumbnail'] = $library['description']['path'].'/medium';
        }else {
            $library['description']['thumbnail'] = '';
        }

        return $library;
    }
}