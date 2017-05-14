<?php

namespace spec\Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelper;
use Fesor\JsonMatcher\Matcher\SizeMatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SizeMatcherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(3);
        $this->setHelper(new JsonHelper());
    }

    function it_verifies_size_of_hash_table()
    {
        $actualJson = '{"foo": 1, "bar": 2, "baz": 3}';

        $this->match($actualJson, true)->shouldReturn(true);
    }

    function it_verifies_size_of_array_by_given_path()
    {
        $actualJson = '{"foo": [1, 2, 3]}';

        $this->at('/foo');
        $this->match($actualJson, true)->shouldReturn(true);
    }

    function it_checks_size_of_string_by_given_path()
    {
        $actualJson = '{"foo": "bar"}';

        $this->at('/foo');
        $this->match($actualJson, true)->shouldReturn(true);
    }
}
