<?php

namespace Fesor\JsonMatcher\Exception;

class PathMatchException extends MatchException
{
    /**
     * PathMatchException constructor.
     * @param string $expectedPath
     * @param array $options
     */
    public function __construct($expectedPath, array $options)
    {
        if (self::isPositive($options)) {
            parent::__construct(sprintf('JSON has no expected JSON path: "%s"', $expectedPath));
        } else {
            parent::__construct(sprintf('JSON should not has JSON path: "%s"', $expectedPath));
        }
    }
}