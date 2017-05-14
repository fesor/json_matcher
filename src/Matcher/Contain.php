<?php

namespace Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelperAwareMatcher;
use Fesor\JsonMatcher\JsonHelperAwareTrait;
use Fesor\JsonMatcher\Matcher;

class Contain implements JsonHelperAwareMatcher
{
    use JsonHelperAwareTrait;

    private $expectedJson;
    private $ignoredKeys;
    private $atPath;

    public function __construct($expectedJson)
    {
        $this->expectedJson = $expectedJson;
        $this->ignoredKeys = [];
    }

    public static function subset(string $subset)
    {
        return new self($subset);
    }

    public function ignoring(string ...$keys): self
    {
        $this->ignoredKeys = $keys;

        return $this;
    }

    public function at(string $path): self
    {
        $this->atPath = $path;

        return $this;
    }

    public function match(string $actualJson, bool $returnResult = false): bool
    {
        $json = $this->helper->normalize($actualJson, $this->atPath);
        $data = $this->helper->parse($json);
        if (!empty($this->ignoredKeys)) {
            $data = $this->helper->excludeKeys($data, $this->ignoredKeys);
        }

        $expectedJson = $this->helper->normalize($this->expectedJson);

        return $this->contains($data, $expectedJson);
    }

    private function contains($haystack, string $needle)
    {
        $parsedJson = $this->helper->parse($needle);
        if (!is_object($haystack) && !is_array($haystack) && is_string($haystack) && is_string($parsedJson)) {
            return false !== strpos($haystack, $parsedJson);
        }

        $normalizedJson = $this->helper->stringify($haystack);
        if ($normalizedJson === $needle) {
            return true;
        }

        if (is_object($haystack)) {
            $haystack = get_object_vars($haystack);
        }

        if (!is_array($haystack)) {
            return false;
        }

        foreach ($haystack as $value) {
            if ($this->contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }
}
