<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class CreateNewLibrary extends Command
{
    /**
     * @var string
     */
    protected $authorId;

    /**
     * @var array
     */
    protected $description;

    /**
     * Create new CreateNewLibrary instance
     * 
     * @param string $authorId
     * @param array  $description
     */
    public function __construct($authorId, $description = [])
    {
        $this->authorId = $authorId;
        $this->description = $description;
    }
}