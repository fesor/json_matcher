<?php

namespace Fesor\JsonMatcher\Exception;

use Fesor\JsonMatcher\JsonMatcher;

class JsonTypeException extends MatchException
{

    /**
     * @param  string $expectedType
     * @param  string $actualType
     * @param  array  $options
     * @return static
     */
    public static function create($expectedType, $actualType, array $options)
    {
        if (self::isPositive($options)) {
            $message = sprintf('Expected JSON value size to be %s, but got %s%s', $expectedType, $actualType, self::getAt($options));
        } else {
            $message = sprintf('Expected JSON value type to not be %s%s', $expectedType, self::getAt($options));
        }

        return new static($message);
    }

}
