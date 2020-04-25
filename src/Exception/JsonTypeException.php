<?php

namespace Fesor\JsonMatcher\Exception;

class JsonTypeException extends MatchException
{
    /**
     * @param string $expectedType
     * @param string $actualType
     *
     * @return static
     */
    public static function create($expectedType, $actualType, array $options)
    {
        if (self::isPositive($options)) {
            $message = sprintf('Expected JSON value type to be %s, but got %s%s', $expectedType, $actualType, self::getAt($options));
        } else {
            $message = sprintf('Expected JSON value type to not be %s%s', $expectedType, self::getAt($options));
        }

        return new static($message);
    }
}
