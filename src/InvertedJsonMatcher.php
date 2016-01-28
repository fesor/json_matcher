<?php

namespace Fesor\JsonMatcher;

class InvertedJsonMatcher implements JsonMatcherInterface
{
    private $matcher;

    public function __construct(JsonMatcher $matcher)
    {
        $this->matcher = $matcher;
    }

    public function equal($expected, array $options = [])
    {
        return $this->matcher->equal($expected, $this->negativeMatch($options));
    }

    public function hasPath($path, array $options = [])
    {
        return $this->matcher->hasPath($path, $this->negativeMatch($options));
    }

    public function hasSize($expectedSize, array $options = [])
    {
        return $this->matcher->hasSize($expectedSize, $this->negativeMatch($options));
    }

    public function hasType($type, array $options = [])
    {
        return $this->matcher->hasType($type, $this->negativeMatch($options));
    }

    public function includes($json, array $options = [])
    {
        return $this->matcher->includes($json, $this->negativeMatch($options));;
    }

    private function negativeMatch(array $options = []) {
        $options[JsonMatcher::OPTION_NEGATIVE] = true;

        return $options;
    }

}
