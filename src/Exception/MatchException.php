<?php

namespace Fesor\JsonMatcher\Exception;

abstract class MatchException extends \RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}
