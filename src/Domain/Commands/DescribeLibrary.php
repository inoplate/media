<?php namespace Inoplate\Media\Domain\Commands;

use Inoplate\Foundation\Domain\Command;

class DescribeLibrary extends Command
{
    /**
     * @var string
     */
    protected $libraryId;

    /**
     * @var array
     */
    protected $description;

    /**
     * Create new DescribeLibrary instance
     * 
     * @param string $libraryId
     * @param array  $description
     */
    public function __construct($libraryId, $description = [])
    {
        $this->libraryId = $libraryId;
        $this->description = $description;
    }
}