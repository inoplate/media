<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Foundation\Jobs\NotifyUser;
use Inoplate\Foundation\App\Services\Bus\Dispatcher as Bus;
use Inoplate\Media\Domain\Events\LibraryWasUnsharedFromAuthor;

class NotifyAuthorMediaUnshared
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
     * @param  LibraryWasUnsharedFromAuthor $event
     * @return void
     */
    public function handle(LibraryWasUnsharedFromAuthor $event)
    {  
        $author = $event->author;
        $library = $event->library;

        $userId = $author->id()->value();
        $owner = $library->owner();
        $message = trans('inoplate-media::messages.library.notification.unshare', 
            [
                'owner' => $owner->name()->value(), 
                'library' => $library->description()->value()['title']]
            );

        $notifyDriver = config('inoplate.media.notifier');
        $this->bus->dispatch(new NotifyUser($userId, $message, $notifyDriver));
    }
}