<?php

namespace Fesor\JsonMatcher\Exception;

class JsonNotMatchesSchemaException  extends MatchException
{
    public static function create($errors, array $options) {

        if (self::isPositive($options)) {
            $message = sprintf("Json doesn't matches schema%s:\n %s", self::getAt($options), self::processErrors($errors));
        } else {
            $message = sprintf('Expected that json%s doesn\'t matches schema', self::getAt($options));
        }

        return new static($message);
    }

    private static function processErrors(array $errors)
    {
        return implode("\n", array_map(function (array $error) {

            return sprintf(' - %s: %s', $error['property'], $error['message']);
        }, $errors));
    }
}