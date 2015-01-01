<?php

namespace Fesor\JsonMatcher\Exception;

use Fesor\JsonMatcher\JsonMatcher;

class JsonEqualityException extends MatchException
{

    /**
     * @param  array  $options
     * @return static
     */
    public static function create(array $options)
    {
        if (self::isPositive($options)) {
            $message = sprintf('Expected inequivalent JSON%s', self::getAt($options));
        } else {
            $message = sprintf('Expected equivalent JSON%s', self::getAt($options));
        }

        return new static($message);
    }

}
