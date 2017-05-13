<?php

namespace Fesor\JsonMatcher;

trait JsonHelperAwareTrait
{
    /**
     * @var JsonHelper
     */
    protected $helper;

    public function setHelper(JsonHelper $helper)
    {
        $this->helper = $helper;
    }
}
