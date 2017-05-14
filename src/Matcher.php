<?php

namespace Fesor\JsonMatcher;

interface Matcher
{
    /**
     * Matches actual json to given rules
     *
     * @param string $actualJson
     * @param bool $returnResult instead of throwing exception of failure
     * @return bool
     */
    public function match(string $actualJson, bool $returnResult = false): bool;
}
