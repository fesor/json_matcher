<?php

namespace Fesor\JsonMatcher\Exception;

class MissingDirectoryException extends JsonMatcherException
{
    public function __construct()
    {
        $this->message = sprintf('Directory not defined');
    }
}
