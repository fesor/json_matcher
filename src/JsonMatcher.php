<?php

namespace Fesor\JsonMatcher;

class JsonMatcher
{
    private $subject;

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Verifies that given json is NOT matches certain rule
     *
     * @param Matcher $matcher
     * @return JsonMatcher
     */
    public function matches(Matcher $matcher): self
    {
        $this->injectHelperIfNeeded($matcher);

        $matcher->match($this->subject, false);

        return $this;
    }

    /**
     * Verifies that given json is NOT matches certain rule
     *
     * @param Matcher $matcher
     * @return JsonMatcher
     */
    public function notMatches(Matcher $matcher): self
    {
        $this->injectHelperIfNeeded($matcher);
        if (!$matcher->match($this->subject, true)) {

        }

        return $this;
    }

    private function injectHelperIfNeeded(Matcher $matcher)
    {
        if (!$matcher instanceof JsonHelperAwareMatcher) {
            return;
        }

        $matcher->setHelper(new JsonHelper());
    }
}
