<?php

namespace Fesor\JsonMatcher;

interface JsonHelperAware
{
    public function setHelper(JsonHelper $normalizer);
}
