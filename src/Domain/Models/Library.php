<?php

namespace Inoplate\Media\Domain\Models;

use Inoplate\Foundation\Domain\Models as FoundationModels;
use Inoplate\Foundation\Domain\Contracts\Describeable;
use Inoplate\Media\Domain\Events;

class Library extends FoundationModels\Entity implements Describeable
{
    use FoundationModels\Describeable;

    /**
     * @var array
     */
    protected $sharedTo;

    /**
     * @var Author
     */
    protected $owner;

    /**
     * Create new Role instance
     * 
     * @param LibraryId                         $id
     * @param Author                            $owner
     * @param array                             $sharedTo
     * @param FoundationModels\Description      $description
     */
    public function __construct(
        LibraryId $id,
        Author $owner,
        $sharedTo = [],
        FoundationModels\Description $description
    ) {
        $this->id = $id;
        $this->owner = $owner;
        $this->sharedTo = $sharedTo;
        $this->description = $description;
    }

    /**
     * Retrieve library owner
     * 
     * @return Author
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * Retrieve to who library shared to
     * 
     * @return array
     */
    public function sharedTo()
    {
        return $this->sharedTo;
    }

    /**
     * Share library to author
     * 
     * @param  Author   $author
     * @return void
     */
    public function share(Author $author)
    {
        if(!in_array($author, $this->sharedTo)) {
            $this->sharedTo[] = $author;
            $this->recordEvent(new Events\LibraryWasSharedToAuthor($this, $author));
        }
    }

    /**
     * Unshare library form author
     * 
     * @param  Author   $author
     * @return void
     */
    public function unshare(Author $author)
    {
        $current = $this->sharedTo;
        $this->sharedTo = array_values(array_filter($this->sharedTo, function($search) use ($author){
            return !$search->equal($author);
        }));

        if($current != $this->sharedTo) {
            $this->recordEvent(new Events\LibraryWasUnsharedFromAuthor($this, $author));
        }        
    }
}