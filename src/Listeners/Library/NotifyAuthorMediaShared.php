<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Foundation\Jobs\NotifyUser;
use Inoplate\Foundation\App\Services\Bus\Dispatcher as Bus;
use Inoplate\Media\Domain\Events\LibraryWasSharedToAuthor;


class NotifyAuthorMediaShared
{
    /**
     * @var Inoplate\Foundation\App\Services\Bus\Dispatcher
     */
    protected $bus;

    /**
     * Create new NotifyAuthorMediaUnshared instance
     *
     * @param Bus $bus
     */
    public function __construct(Bus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Handle event
     * 
     * @param  LibraryWasSharedToAuthor $event
     * @return void
     */
    public function handle(LibraryWasSharedToAuthor $event)
    {  
        $author = $event->author;
        $library = $event->library;

        $userId = $author->id()->value();
        $owner = $library->owner();
        $message = trans('inoplate-media::messages.library.notification.share', 
            [
                'owner' => $owner->name()->value(), 
                'library' => $library->description()->value()['title']]
            );

        $notifyDriver = config('inoplate.media.notifier');
        $this->bus->dispatch(new NotifyUser($userId, $message, $notifyDriver));
    }
}