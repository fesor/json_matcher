<?php

namespace spec\Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\Matcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotSpec extends ObjectBehavior
{
    function it_inverts_results_of_passed_matcher(Matcher $matcher)
    {
        $matcher->match('actual', true)->willReturn(true);

        $this->beConstructedWith($matcher);
        $this->match('actual', false)->shouldReturn(false);
    }
}
