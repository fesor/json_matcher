<?php


namespace Fesor\JsonMatcher\Exception;


class JsonSchemaDoesntMatchException extends MatchException
{
    public static function create(array $options, array $errors)
    {
        if (self::isPositive($options)) {
            $message = sprintf('Expected JSON to match schema%s', self::getAt($options));
        } else {
            $message = sprintf('Expected JSON not to match schema%s', self::getAt($options));
        }

        return new static($message);
    }
}