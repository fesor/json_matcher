<?php

namespace Fesor\JsonMatcher;

class JsonMatcherFactory
{

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var array of excluded by default keys
     */
    private $excludedKeys;

    /**
     * @param JsonHelper $jsonHelper
     * @param array $excludedKeys
     */
    public function __construct(JsonHelper $jsonHelper, array $excludedKeys = [])
    {
        $this->jsonHelper = $jsonHelper;
        $this->excludedKeys = $excludedKeys;
    }

    /**
     * Creates instance of matcher with given subject
     *
     * @param string $subject
     * @return JsonMatcher
     */
    public function create($subject)
    {
        $matcher = new JsonMatcher($this->jsonHelper, $this->excludedKeys);
        
        return $matcher->setSubject($subject);
    }
    
}