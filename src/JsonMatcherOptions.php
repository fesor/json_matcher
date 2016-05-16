<?php

namespace Fesor\JsonMatcher;

final class JsonMatcherOptions
{
    private $expectedJson;

    private $negative;

    private $path;

    private $excludedKeys;

    private $includedKeys;

    /**
     * JsonMatcherOptions constructor.
     * @param string|null $expectedJson
     */
    public function __construct($expectedJson = null)
    {
        $this->expectedJson = $expectedJson;
    }

    public function atPath($path) {
        $this->path = $path;
        return $this;
    }

    public function including() {
        $this->includedKeys = func_get_args();
        return $this;
    }

    public function skipping() {
        $this->excludedKeys = func_get_args();
        return $this;
    }

    public static function nagative(JsonMatcherOptions $options)
    {
        $options->negative = true;
    }
}
