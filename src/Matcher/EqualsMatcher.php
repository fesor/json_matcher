<?php

namespace Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelperAware;
use Fesor\JsonMatcher\JsonHelperAwareTrait;

class EqualsMatcher implements JsonHelperAware
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

    public static function to(string $expectedJson): self
    {
        return new self($expectedJson);
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
        if (!empty($this->ignoredKeys)) {
            $json = $this->helper->excludeKeysFromJson($json, $this->ignoredKeys);
        }

        $expectedJson = $this->helper->normalize($this->expectedJson);

        return $expectedJson === $json;
    }
}
