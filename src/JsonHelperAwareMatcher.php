<?php

namespace Fesor\JsonMatcher;

interface JsonHelperAwareMatcher extends Matcher
{
    public function setHelper(JsonHelper $normalizer);
}
