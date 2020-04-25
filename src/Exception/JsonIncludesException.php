<?php

namespace Fesor\JsonMatcher\Exception;

class JsonIncludesException extends MatchException
{
    /**
     * @return static
     */
    public static function create(array $options)
    {
        if (self::isPositive($options)) {
            $message = 'Expected included JSON%s';
        } else {
            $message = 'Expected excluded JSON%s';
        }

        return new static(sprintf($message, self::getAt($options)));
    }
}
