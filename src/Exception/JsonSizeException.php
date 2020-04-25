<?php

namespace Fesor\JsonMatcher\Exception;

class JsonSizeException extends MatchException
{
    /**
     * @param int $expectedSize
     * @param int $actualSize
     *
     * @return static
     */
    public static function create($expectedSize, $actualSize, array $options)
    {
        if (self::isPositive($options)) {
            $message = sprintf('Expected JSON value size to be %d, but got %d%s', $expectedSize, $actualSize, self::getAt($options));
        } else {
            $message = sprintf('Expected JSON value size to not be %d%s', $expectedSize, self::getAt($options));
        }

        return new static($message);
    }
}
