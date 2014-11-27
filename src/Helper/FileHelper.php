<?php

namespace Fesor\JsonMatcher\Helper;

use Fesor\JsonMatcher\Exception\MissingDirectoryException;
use Fesor\JsonMatcher\Exception\MissingFileException;

class FileHelper
{

    private $directory;

    /**
     * @param string $directory
     */
    public function __construct($directory = null)
    {
        $this->setDirectory($directory);
    }

    /**
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        if (!is_null($directory)) {
            $this->directory = rtrim($directory, '/').'/';
        }
    }

    /**
     * @param  string $path
     * @return string
     */
    public function loadJson($path)
    {
        if (!$this->directory) {
            throw new MissingDirectoryException();
        }

        $path = $this->directory . $path;
        if (!is_file($path)) {
            throw new MissingFileException($path);
        }

        return file_get_contents($path);
    }

}
