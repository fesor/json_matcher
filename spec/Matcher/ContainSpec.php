<?php

namespace spec\Fesor\JsonMatcher\Matcher;

use Fesor\JsonMatcher\JsonHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainSpec extends ObjectBehavior
{
    function it_checks_for_subset_of_data_in_given_json()
    {
        $actualJson = '{
            "data": [
                {"id": 1, "name": "John Doe"},
                {"id": 2, "name": "Jane Doe"}
            ]
        }';

        $this->specifySubset('{"id": 1, "name": "John Doe"}');

        $this->match($actualJson, true)->shouldReturn(true);
    }

    function it_checks_for_subset_of_data_ignoring_specific_keys()
    {
        $actualJson = '{
            "data": [
                {"id": 1, "name": "John Doe"},
                {"id": 2, "name": "Jane Doe"}
            ]
        }';

        $this->specifySubset('{"name": "Jane Doe"}');

        $this->ignoring('id');
        $this->match($actualJson, true)->shouldReturn(true);
    }

    function it_allows_to_check_for_substrings_as_subset_of_data()
    {
        $this->specifySubset('"Doe"');

        $this->match('{"id": 1, "name": "John Doe"}', true)->shouldReturn(true);
    }

    function it_respects_type_of_value()
    {
        $this->specifySubset('0');

        $this->match('[false, 0]', true)->shouldReturn(true);
        $this->match('[false, "0"]', true)->shouldReturn(false);
    }

    private function specifySubset(string $expectedSubset)
    {
        $this->beConstructedThrough('subset', [$expectedSubset]);
        $this->setHelper(new JsonHelper());
    }
}
