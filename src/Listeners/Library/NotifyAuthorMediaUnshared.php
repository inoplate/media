<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Notifier\Laravel\NotifierFactory;
use Inoplate\Media\Domain\Events\LibraryWasUnsharedFromAuthor;

class NotifyAuthorMediaUnshared
{
    /**
     * @var Inoplate\Media\Domain\Events\LibraryWasUnsharedFromAuthor
     */
    protected $notifierFactory;

    /**
     * Create new NotifyAuthorMediaUnshared instance
     * 
     * @param NotifierFactory $notifierFactory
     */
    public function __construct(NotifierFactory $notifierFactory)
    {
        $this->notifierFactory = $notifierFactory;
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

        $this->notifierFactory->drive('database')
             ->notify($message, $userId);
    }
}