<?php

namespace Fesor\JsonMatcher\Exception;

use Fesor\JsonMatcher\JsonMatcher;

abstract class MatchException extends \RuntimeException
{

    /**
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @param  array  $options
     * @return string
     */
    protected static function getAt(array $options)
    {
        if (empty($options[JsonMatcher::OPTION_PATH])) {
            return '';
        }

        return sprintf(' at \'%s\'', $options[JsonMatcher::OPTION_PATH]);
    }

    /**
     * @param  array $options
     * @return bool
     */
    protected static function isPositive(array $options)
    {
        return empty($options[JsonMatcher::OPTION_NEGATIVE]);
    }

}
