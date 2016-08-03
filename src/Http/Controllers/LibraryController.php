<?php

namespace Inoplate\Media\Http\Controllers;

use Inoplate\Media\Services\Uploader\Receiver;
use Inoplate\Foundation\Http\Controllers\Controller;
use Inoplate\Media\Domain\Repositories\Library as LibraryRepository;
use Inoplate\Media\Domain\Commands;
use Inoplate\Media\Domain\Models as MediaDomainModels;
use Inoplate\Account\User as UserEloquent;
use Inoplate\Foundation\Domain\Models as FoundationDomainModels;
use Inoplate\Foundation\App\Services\Bus\Dispatcher as Bus;
use Inoplate\Foundation\App\Services\Events\Dispatcher as Events;
use Inoplate\Media\Resources\Library as LibraryResource;
use Inoplate\Media\Events\FileWasFailedToUpload;
use Inoplate\Media\Exceptions;
use Roseffendi\Authis\Authis;
use Inoplate\Media\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class LibraryController extends Controller
{
    protected $libraryRepository;

    protected $filesystemFactory;

    protected $authis;

    public function __construct(
        LibraryRepository $libraryRepository, 
        FilesystemFactory $filesystemFactory,
        Authis $authis
    ) {
        $this->libraryRepository = $libraryRepository;
        $this->filesystemFactory = $filesystemFactory;
        $this->authis = $authis;
    }

    public function getIndex(Request $request)
    {
        $page = $request->input('page') ?: 1;
        $visibility = $request->input('visibility');
        $ownership = $request->input('ownership');
        $search = $request->input('search');

        $perPage = config('inoplate.media.library.per_page', 10);
        $items = collect($this->libraryRepository->get($page, $search, $visibility, $ownership));
        $total = $this->libraryRepository->count();

        $items->transform(function($item, $key){
            return $this->generateReturnedData($item->toArray());
        });

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page);
        $paginator->setPath('/admin/inoplate-media/libraries');
        
        return $this->getResponse('inoplate-media::library.index', ['libraries' => $paginator->toArray()]);
    }

    public function upload(
        Request $request, 
        Bus $bus, 
        Receiver $receiver, 
        Events $events, 
        MessageBag $messageBag
    ) {
        try{
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
        }catch(Exceptions\MaximumUploadSizeExceededException $e) {
            $events->fire(new FileWasFailedToUpload($e->getUploadedFiles()));

            $messageBag->add('file', trans('inoplate-media::messages.library.file_too_large', ['size' => config('inoplate.media.library.size.max').'M']));
            return $this->formError(422, $messageBag->toArray());
        }catch(Exceptions\UnallowedFileExtensionException $e) {
            $events->fire(new FileWasFailedToUpload($e->getUploadedFiles()));
            $meta = $e->getLibraryMeta();

            $messageBag->add('file', trans('inoplate-media::messages.library.invalid_extension', ['extension' => $meta['extension'] ]));
            return $this->formError(422, $messageBag->toArray());
        }
    }

    public function putUpdate(Request $request, Bus $bus, $library)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $id = $library->id;

        $description = array_except( $library->toArray(), ['id', 'user', 'users'] );
        $description['title'] = $request->input('title');
        $description['description'] = $request->input('description');

        $bus->dispatch(new Commands\DescribeLibrary($id, $description));

        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($id));
        return $this->formSuccess(
            route('media.admin.libraries.update.get', ['id' => $id]), 
            [
                'message' => trans('inoplate-media::messages.library.updated'), 
                'library' => $this->generateReturnedData($library->toArray())
            ]
        );
    }

    public function putPublish(Request $request, Bus $bus, $library)
    {
        $id = $library->id;

        $description = array_except( $library->toArray(), ['id', 'user', 'users'] );
        $description['visibility'] = 'public';

        $bus->dispatch(new Commands\DescribeLibrary($id, $description));

        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($id));
        return $this->formSuccess(
            route('media.admin.libraries.update.get', ['id' => $id]), 
            [
                'message' => trans('inoplate-media::messages.library.published'), 
                'library' => $this->generateReturnedData($library->toArray())
            ]
        );
    }

    public function putUnpublish(Request $request, Bus $bus, $library)
    {
        $id = $library->id;

        $description = array_except( $library->toArray(), ['id', 'user', 'users'] );
        $description['visibility'] = 'public';

        $bus->dispatch(new Commands\DescribeLibrary($id, $description));
        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($id));
        return $this->formSuccess(
            route('media.admin.libraries.update.get', ['id' => $id]), 
            [
                'message' => trans('inoplate-media::messages.library.unpublished'), 
                'library' => $this->generateReturnedData($library->toArray())
            ]
        );
    }

    public function putShare(Request $request, Bus $bus, $library)
    {
        $id = $library->id;
        $userIds = $request->input('authors') ?: [];
        $sharedTo = $library->users;
        $unshared = 0;

        foreach ($userIds as $userId) {
            $bus->dispatch( new Commands\ShareLibraryToAuthor($id, $userId) );
        }

        foreach ($sharedTo as $user) {
            $userId = $user->id;
            if(!in_array($userId, $userIds)) {
                $bus->dispatch( new Commands\UnshareLibraryFromAuthor($id, $userId) );
                ++$unshared;
            }
        }

        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($id));

        return $this->formSuccess(
            route('media.admin.libraries.update.get', ['id' => $id]), 
            [
                'message' => trans('inoplate-media::messages.library.shared', ['shared' => count($userIds), 'unshared' => $unshared]), 
                'library' => $this->generateReturnedData($library->toArray())
            ]
        );
    }

    public function getShareableUsers(Request $request, $library)
    {
        $search = $request->input('search');
        $page = $request->input('page');

        $users = UserEloquent::where('name', 'like', "%$search%")
                              ->where('id', '!=', $library->user->id)
                              ->select(['id', 'name'])
                              ->paginate(5, $page);

        return $users;
    }

    public function delete(Bus $bus, $library)
    {
        $id = $library->id;
        $library = $this->libraryRepository->findById(new MediaDomainModels\LibraryId($id));

        $bus->dispatch(new Commands\DeleteLibrary($id));

        return $this->formSuccess(
            route('media.admin.libraries.index.get'), 
            [
                'message' => trans('inoplate-media::messages.library.deleted'),
                'library' => $library->toArray()
            ]
        );
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

        $library['description']['mime'] = $filesystem->exists($library['description']['path']) ? $filesystem->mimeType($library['description']['path']) : 'NaN';
        $library['description']['size'] = $filesystem->exists($library['description']['path']) ? $filesystem->size($library['description']['path']) : 'NaN';
        
        if(is_image($library['description']['mime'])) {
            $library['description']['thumbnail'] = $library['description']['path'].'/medium';
        }else {
            $library['description']['thumbnail'] = '';
        }

        $resourceChecker = $this->authis->forResource(MediaLibrary::find($library['id']));

        $library['description']['shareable'] = $resourceChecker->check('media.admin.libraries.share.get');
        $library['description']['updateable'] = $resourceChecker->check('media.admin.libraries.update.get');
        $library['description']['publishable'] = $resourceChecker->check('media.admin.libraries.manage-publishment.get');
        $library['description']['deletable'] = $resourceChecker->check('media.admin.libraries.delete.get');

        return $library;
    }
}