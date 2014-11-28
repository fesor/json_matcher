<?php

namespace spec\Fesor\JsonMatcher;

use \Fesor\JsonMatcher\Helper\JsonHelper;
use PhpSpec\ObjectBehavior;
use Seld\JsonLint\JsonParser;

class JsonMatcherSpec extends ObjectBehavior
{

    function let()
    {
        $this->beConstructedWith(new JsonHelper(new JsonParser()), ['id']);
    }

    // <editor-fold desc="Negative matching">
    function it_supports_negative_matching()
    {
        $json = '{"json": "spec"}';
        $this($json)->shouldNotThrow()->duringNotEqual($json);
    }

    function it_checks_is_matcher_supported()
    {
        $this->shouldThrow(new \RuntimeException('Matcher "match" not supported'))->duringNotMatch();
    }

    function it_checks_is_method_exists()
    {
        $this->shouldThrow(new \RuntimeException('Method "match" not exists'))->duringMatch();
    }

    function it_validates_argument_count()
    {
        $this->shouldThrow(new \RuntimeException('Matcher requires one argument'))->duringNotEqual();
    }
    // </editor-fold>

    // <editor-fold desc="equal spec">
    function it_matches_identical_JSON()
    {
        $this('{"json":"spec"}')->equal('{"json":"spec"}')->shouldBe(true);
    }

      function it_matches_not_identical_JSON_for_nagetive_matching()
      {
          $this('{"json":"spec"}')->notEqual('{"spec":"json"}')->shouldBe(true);
      }

    function it_matches_differently_formatted_JSON()
    {
        $this('{"json": "spec"}')->equal('{"json":"spec"}')->shouldBe(true);
    }

    function it_matches_out_of_order_hashes()
    {
        $this('{"laser":"lemon","json":"spec"}')->equal('{"json":"spec","laser":"lemon"}')->shouldBe(true);
    }

    function it_does_not_match_out_of_order_arrays()
    {
        $this('["json","spec"]')->equal('["spec", "json"]')->shouldBe(false);
    }

    function it_does_match_out_of_order_arrays_on_negative()
    {
        $this('["json","spec"]')->notEqual('["spec", "json"]')->shouldBe(true);
    }

    function it_matches_valid_JSON_values_yet_invalid_JSON_documents()
    {
        $this('"json_spec"')->equal('"json_spec"')->shouldBe(true);
    }

    function it_matches_at_a_path()
    {
        $this('{"json":["spec"]}')->equal('"spec"', ['at' => 'json/0'])->shouldBe(true);
    }

    function it_ignores_excluded_by_default_hash_keys()
    {
        $this('{"id": 1, "json":["spec"]}')->equal('{"id": 2, "json":["spec"]}')->shouldBe(true);
    }

    function it_not_ignores_excluded_by_default_hash_keys_if_it_setted_as_included()
    {
        $this('{"id": 1, "json":["spec"]}')->equal('{"id": 2, "json":["spec"]}', [
            'including' => ['id']
        ])->shouldBe(false);
    }

    function it_ignores_custom_excluded_hash_keys()
    {
        $this('{"json":"spec","ignore":"please"}')->equal('{"json":"spec"}', [
            'excluding' => ['ignore']
        ])->shouldBe(true);
    }

    function it_ignores_nested_excluded_hash_keys()
    {
        $this('{"json":"spec","please":{"ignore":"this"}}')->equal('{"json":"spec","please":{}}', [
            'excluding' => ['ignore']
        ])->shouldBe(true);
    }

    function it_ignores_hash_keys_when_included_in_the_expected_value()
    {
        $this('{"json":"spec","ignore":"please"}')->equal('{"json":"spec","ignore":"this"}', [
            'excluding' => ['ignore']
        ])->shouldBe(true);
    }

    function it_matches_different_looking_JSON_equivalent_values()
    {
        $this('{"ten":10.0}')->equal('{"ten":1e+1}')->shouldBe(true);
    }

    function it_excludes_multiple_keys()
    {
        $this('{"id":1,"json":"spec"}')->equal('{"id":2,"json":"different"}', [
            'excluding' => ['id', 'json']
        ])->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="havePath spec">
    function it_matches_hash_keys()
    {
        $this('{"one":{"two":{"three":4}}}')->havePath('one/two/three')->shouldBe(true);
    }

    function it_does_not_match_values()
    {
        $this('{"one":{"two":{"three":4}}}')->havePath('one/two/three/4')->shouldBe(false);
    }

    function it_matches_array_indexes()
    {
        $this('[1,[1,2,[1,2,3,4]]]')->havePath('1/2/3')->shouldBe(true);
    }

    function it_respects_null_array_values()
    {
        $this('[null,[null,null,[null,null,null,null]]]')->havePath('1/2/3')->shouldBe(true);
    }

    function it_matches_hash_keys_and_array_indexes()
    {
        $this('{"one":[1,2,{"three":4}]}')->havePath('one/2/three')->shouldBe(true);
    }

    function it_matches_hash_keys_with_given_base_path()
    {
        $this('{"one":{"two":{"three":4}}}')->havePath('two/three', ['at' => 'one'])->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="haveSize spec">
    function it_counts_array_entries()
    {
        $this('[1,2,3]')->haveSize(3)->shouldBe(true);
    }

    function it_counts_null_array_entries()
    {
        $this('[1,null,3]')->haveSize(3)->shouldBe(true);
    }

    function it_counts_hash_key_value_pairs()
    {
        $this('{"one":1,"two":2,"three":3}')->haveSize(3)->shouldBe(true);
    }

    function it_counts_null_hash_values()
    {
        $this('{"one":1,"two":null,"three":3}')->haveSize(3)->shouldBe(true);
    }

    function it_matches_size_at_a_path()
    {
        $this('{"one":[1,2,3]}')->haveSize(3, ['at' => 'one'])->shouldBe(true);
    }

    function it_cant_match_size_of_scalars()
    {
        $this('{"one":[1,2,3]}')->haveSize(3, ['at' => 'one/0'])->shouldBe(false);
    }
    //</editor-fold>

    // <editor-fold desc="haveType spec">
    function it_matches_objects()
    {
        $this('{}')->haveType('object')->shouldBe(true);
    }

    function it_matches_arrays()
    {
        $this('[]')->haveType('array')->shouldBe(true);
    }

    function it_matches_type_at_a_path()
    {
        $this('{"root":[]}')->haveType('array', [
            'at' => 'root'
        ])->shouldBe(true);
    }

    function it_matches_strings()
    {
        $this('["json_spec"]')->haveType('string', ['at' => '0'])->shouldBe(true);
    }

    function it_matches_a_valid_JSON_value_yet_invalid_JSON_document()
    {
        $this('"json_spec"')->haveType('string')->shouldBe(true);
    }

    function it_matches_empty_strings()
    {
        $this('""')->haveType('string')->shouldBe(true);
    }

    function it_matches_integers()
    {
        $this('10')->haveType('integer')->shouldBe(true);
    }

    function it_matches_floats()
    {
        $this('10.0')->haveType('float')->shouldBe(true);
        $this('1e+1')->haveType('float')->shouldBe(true);
    }

    function it_matches_booleans()
    {
        $this('true')->haveType('boolean')->shouldBe(true);
        $this('false')->haveType('boolean')->shouldBe(true);
    }
    //</editor-fold>

    // <editor-fold desc="includes spec">
    function it_matches_included_array_elements()
    {
        $json = '["one",1,1.0,true,false,null]';
        $this($json)->includes('"one"')->shouldReturn(true);
        $this($json)->includes('1')->shouldReturn(true);
        $this($json)->includes('1.0')->shouldReturn(true);
        $this($json)->includes('true')->shouldReturn(true);
        $this($json)->includes('false')->shouldReturn(true);
        $this($json)->includes('null')->shouldReturn(true);
    }

    function it_matches_an_array_included_in_an_array()
    {
        $json = '[[1,2,3],[4,5,6]]';
        $this($json)->includes('[1, 2, 3]')->shouldReturn(true);
        $this($json)->includes('[4, 5, 6]')->shouldReturn(true);
    }

    function it_matches_a_hash_included_in_an_array()
    {
        $json = '[{"one":1},{"two":2}]';
        $this($json)->includes('{"one":1}')->shouldReturn(true);
        $this($json)->includes('{"two":2}')->shouldReturn(true);
    }

    function it_matches_included_hash_values()
    {
        $json = '{"string":"one","integer":1,"float":1.0,"true":true,"false":false,"null":null}';
        $this($json)->includes('"one"')->shouldReturn(true);
        $this($json)->includes('1')->shouldReturn(true);
        $this($json)->includes('1.0')->shouldReturn(true);
        $this($json)->includes('true')->shouldReturn(true);
        $this($json)->includes('false')->shouldReturn(true);
        $this($json)->includes('null')->shouldReturn(true);
    }

    function it_matches_a_hash_included_in_a_hash()
    {
        $json = '{"one":{"two":3},"four":{"five":6}}';
        $this($json)->includes('{"two":3}')->shouldReturn(true);
        $this($json)->includes('{"five":6}')->shouldReturn(true);
    }

    function it_matches_an_array_included_in_a_hash()
    {
        $json = '{"one":[2,3],"four":[5,6]}';
        $this($json)->includes('[2,3]')->shouldReturn(true);
        $this($json)->includes('[5,6]')->shouldReturn(true);
    }

    function it_matches_a_substring()
    {
        $json = '"json"';
        $this($json)->includes('"js"')->shouldReturn(true);
        $this($json)->includes('"json"')->shouldReturn(true);
    }

    function it_matches_t_a_path()
    {
        $json = '{"one":{"two":[3,4]}}';
        $this($json)->includes('[3,4]', ['at' => 'one'])->shouldReturn(true);
    }

    function it_ignores_excluded_keys()
    {
        $json = '[{"id":1,"two":3}]';
        $this($json)->includes('{"two":3}')->shouldReturn(true);
    }
    // </editor-fold>

}
