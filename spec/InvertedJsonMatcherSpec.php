<?php

namespace spec\Fesor\JsonMatcher;

use Fesor\JsonMatcher\JsonMatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InvertedJsonMatcherSpec extends ObjectBehavior
{
    private $baseMatcher;

    function let(JsonMatcher $baseMatcher)
    {
        $this->baseMatcher = $baseMatcher;

        $this->beConstructedWith($baseMatcher);
    }

    function it_inverts_equal_matcher_results_from_negative_to_positive()
    {
        $this->baseMatcherCalled('equal');

        $this->equal('fake', [])->shouldReturn($this->baseMatcher);
    }

    function it_inverts_includes_matcher_results_from_negative_to_positive()
    {
        $this->baseMatcherCalled('includes');

        $this->includes('fake', [])->shouldReturn($this->baseMatcher);
    }

    function it_inverts_hasPath_matcher_results_from_negative_to_positive()
    {
        $this->baseMatcherCalled('hasPath');

        $this->hasPath('fake', [])->shouldReturn($this->baseMatcher);
    }

    function it_inverts_hasType_matcher_results_from_negative_to_positive()
    {
        $this->baseMatcherCalled('hasType');

        $this->hasType('fake', [])->shouldReturn($this->baseMatcher);
    }

    function it_inverts_hasSize_matcher_results_from_negative_to_positive()
    {
        $this->baseMatcherCalled('hasSize');

        $this->hasSize('fake', [])->shouldReturn($this->baseMatcher);
    }

    private function baseMatcherCalled($name) {
        call_user_func([$this->baseMatcher, $name], 'fake', [JsonMatcher::OPTION_NEGATIVE => true])
            ->willReturn($this->baseMatcher);
    }
}
