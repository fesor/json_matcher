<?php

namespace spec\Fesor\JsonMatcher;

use Fesor\JsonMatcher\JsonHelper;
use Fesor\JsonMatcher\JsonHelperAwareMatcher;
use Fesor\JsonMatcher\Matcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonMatcherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('subject');
    }

    function it_injects_helper_in_matcher_if_it_requires_it(JsonHelperAwareMatcher $matcher)
    {
        $matcher->match('subject', false)->willReturn(true);
        $matcher->setHelper(Argument::type(JsonHelper::class))->shouldBeCalled();

        $this->shouldNotThrow()->during('should', [$matcher]);
    }

    function it_uses_given_matcher_to_validate_subject(Matcher $matcher)
    {
        $matcher->match('subject', false)->willReturn(false);

        $this->shouldNotThrow()->during('should', [$matcher]);
    }

    function it_allows_to_use_negative_matching(Matcher $matcher)
    {
        $matcher->match('subject', true)->willReturn(true);

        $this->shouldNotThrow()->during('shouldNot', [$matcher]);
    }
}
