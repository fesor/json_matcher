<?php

namespace spec\Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelper;
use PhpSpec\ObjectBehavior;

class EqualsMatcherSpec extends ObjectBehavior
{
    function let(JsonHelper $helper)
    {
        $this->beConstructedWith('expected json');
        $this->setHelper($helper);

        $helper->normalize('actual json', null)->willReturn('normalized json');
        $helper->normalize('expected json', null)->willReturn('normalized json');
    }

    function it_matches_that_two_jsons_are_equal()
    {
        $this->match('actual json')->shouldReturn(true);
    }

    function it_matches_json_by_given_path(JsonHelper $helper)
    {
        $helper->normalize('actual json', '/foo')->willReturn('normalized json');

        $this->at('/foo');
        $this->match('actual json')->shouldReturn(true);
    }

    function it_ignores_specific_keys_on_compartion(JsonHelper $helper)
    {
        $helper->normalize('actual json', '/foo')->willReturn('actual json');
        $helper->excludeKeysFromJson('normalized json', ['foo', 'bar'])->willReturn('normalized json');

        $this->ignoring('foo', 'bar');
        $this->match('actual json')->shouldReturn(true);
    }

    function it_returns_false_on_error_if_return_value_is_required(JsonHelper $helper)
    {
        $helper->normalize('actual json', null)->willReturn('different json');

        $this->match('actual json', true)->shouldReturn(false);
    }

    function it_throws_error_if_jsons_are_not_equal_and_no_return_value_required(JsonHelper $helper)
    {
        $helper->normalize('actual json', null)->willReturn('different json');

        $this->match('actual json');
    }
}
