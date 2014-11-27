<?php

namespace Fesor\JsonMatcher\Exception;

class MissingFileException extends JsonMatcherException
{
    public function __construct($path)
    {
        $this->message = sprintf('File `%s` is not exists', $path);
    }
}
