<?php

namespace Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelperAware;
use Fesor\JsonMatcher\JsonHelperAwareTrait;
use Fesor\JsonMatcher\Matcher;

class SizeMatcher implements JsonHelperAware, Matcher
{
    use JsonHelperAwareTrait;

    private $atPath;
    private $expectedSize;

    public function __construct(int $expectedSize)
    {
        $this->expectedSize = $expectedSize;
    }

    public function at(string $path)
    {
        $this->atPath = $path;
    }

    public function match(string $actualJson, bool $returnResult = false): bool
    {
        $parsed = $this->helper->parse($actualJson, $this->atPath);

        if (is_string($parsed)) {
            $size = mb_strlen($parsed);
        } else if (is_object($parsed)) {
            $size = count(get_object_vars($parsed));
        } else {
            $size = count($parsed);
        }

        return $size === $this->expectedSize;
    }
}
