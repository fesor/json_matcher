<?php

namespace Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\Matcher;

class Not implements Matcher
{
    /**
     * @var Matcher
     */
    private $matcher;

    public function __construct(Matcher $matcher)
    {
        $this->matcher = $matcher;
    }

    public function match(string $actualJson, bool $returnResult = false): bool
    {
        return !$this->matcher->match($actualJson, true);
    }
}
