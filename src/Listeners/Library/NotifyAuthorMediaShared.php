<?php

namespace Inoplate\Media\Listeners\Library;

use Inoplate\Notifier\Laravel\NotifierFactory;
use Inoplate\Media\Domain\Events\LibraryWasSharedToAuthor;

class NotifyAuthorMediaShared
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

        $this->notifierFactory->drive('database')
             ->notify($message, $userId);
    }
}