<?php

namespace spec\Fesor\JsonMatcher;

use Fesor\JsonMatcher\JsonHelper;
use PhpSpec\ObjectBehavior;

class JsonMatcherSpec extends ObjectBehavior
{
    
    private static $equalityException = 'Fesor\\JsonMatcher\\Exception\\JsonEqualityException';
    private static $missingPathException = 'Fesor\\JsonMatcher\\Exception\\MissingPathException';
    private static $pathMatchException = 'Fesor\JsonMatcher\Exception\PathMatchException';
    private static $jsonTypeException = 'Fesor\\JsonMatcher\\Exception\\JsonTypeException';
    private static $jsonSizeException = 'Fesor\\JsonMatcher\\Exception\\JsonSizeException';
    private static $jsonIncludesException = 'Fesor\\JsonMatcher\\Exception\\JsonIncludesException';

    function let()
    {
        $this->beConstructedWith(new JsonHelper(), ['id']);
    }

    // <editor-fold desc="Negative matching">
    function it_supports_negative_matching()
    {
        $json = '{"json": "spec"}';
        $this->setSubject($json)->shouldThrow(self::$equalityException)->duringNotEqual($json);
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
        $this->shouldThrow(new \RuntimeException('Matcher requires at least one argument'))->duringNotEqual();
    }
    // </editor-fold>

    // <editor-fold desc="equal spec">
    function it_matches_identical_JSON()
    {
        $this->setSubject(('{"json":"spec"}'))->shouldNotThrow()->duringEqual('{"json":"spec"}');
    }

    function it_matches_not_identical_JSON_for_nagetive_matching()
    {
        $this->setSubject(('{"json":"spec"}'))->shouldNotThrow()->duringNotEqual('{"spec":"json"}');
    }

    function it_matches_differently_formatted_JSON()
    {
        $this->setSubject(('{"json": "spec"}'))->shouldNotThrow()->duringEqual('{"json":"spec"}');
    }

    function it_matches_out_of_order_hashes()
    {
        $this->setSubject(('{"laser":"lemon","json":"spec"}'))->shouldNotThrow()->duringEqual('{"json":"spec","laser":"lemon"}');
    }

    function it_does_not_match_out_of_order_arrays()
    {
        $this->setSubject(('["json","spec"]'))->shouldThrow(self::$equalityException)->duringEqual('["spec", "json"]');
    }

    function it_does_match_out_of_order_arrays_on_negative()
    {
        $this->setSubject(('["json","spec"]'))->shouldNotThrow()->duringNotEqual('["spec", "json"]');
    }

    function it_matches_valid_JSON_values_yet_invalid_JSON_documents()
    {
        $this->setSubject(('"json_spec"'))->shouldNotThrow()->duringEqual('"json_spec"');
    }

    function it_matches_at_a_path()
    {
        $this->setSubject(('{"json":["spec"]}'))->shouldNotThrow()->duringEqual('"spec"', ['at' => 'json/0']);
    }

    function it_ignores_excluded_by_default_hash_keys()
    {
        $this->setSubject(('{"id": 1, "json":["spec"]}'))->shouldNotThrow()->duringEqual('{"id": 2, "json":["spec"]}');
    }

    function it_not_ignores_excluded_by_default_hash_keys_if_it_setted_as_included()
    {
        $this->setSubject(('{"id": 1, "json":["spec"]}'))
            ->shouldThrow(self::$equalityException)
            ->duringEqual('{"id": 2, "json":["spec"]}', [
                'including' => ['id']
            ])
        ;
    }

    function it_ignores_custom_excluded_hash_keys()
    {
        $this->setSubject(('{"json":"spec","ignore":"please"}'))
            ->shouldNotThrow()
            ->duringEqual('{"json":"spec"}', [
                'excluding' => ['ignore']
            ])
        ;
    }

    function it_ignores_nested_excluded_hash_keys()
    {
        $this->setSubject(('{"json":"spec","please":{"ignore":"this"}}'))
            ->shouldNotThrow()
            ->duringEqual('{"json":"spec","please":{}}', [
                'excluding' => ['ignore']
            ])
        ;
    }

    function it_ignores_hash_keys_when_included_in_the_expected_value()
    {
        $this->setSubject(('{"json":"spec","ignore":"please"}'))
            ->shouldNotThrow()
            ->duringEqual('{"json":"spec","ignore":"this"}', [
                'excluding' => ['ignore']
            ])
        ;
    }

    function it_matches_different_looking_JSON_equivalent_values()
    {
        $this->setSubject(('{"ten":10.0}'))->shouldNotThrow()->duringEqual('{"ten":1e+1}');
    }

    function it_excludes_multiple_keys()
    {
        $this->setSubject(('{"id":1,"json":"spec"}'))->shouldNotThrow()->duringEqual('{"id":2,"json":"different"}', [
            'excluding' => ['id', 'json']
        ]);
    }
    //</editor-fold>

    // <editor-fold desc="hasPath spec">
    function it_matches_hash_keys()
    {
        $this->setSubject(('{"one":{"two":{"three":4}}}'))->shouldNotThrow()->duringHasPath('one/two/three');
    }

    function it_does_not_match_values()
    {
        $this->setSubject(('{"one":{"two":{"three":4}}}'))->shouldThrow(self::$pathMatchException)->duringHasPath('one/two/three/4');
    }

    function it_matches_array_indexes()
    {
        $this->setSubject(('[1,[1,2,[1,2,3,4]]]'))->shouldNotThrow()->duringHasPath('1/2/3');
    }

    function it_respects_null_array_values()
    {
        $this->setSubject(('[null,[null,null,[null,null,null,null]]]'))->shouldNotThrow()->duringHasPath('1/2/3');
    }

    function it_matches_hash_keys_and_array_indexes()
    {
        $this->setSubject(('{"one":[1,2,{"three":4}]}'))->shouldNotThrow()->duringHasPath('one/2/three');
    }

    function it_matches_hash_keys_with_given_base_path()
    {
        $this->setSubject(('{"one":{"two":{"three":4}}}'))->shouldNotThrow()->duringHasPath('two/three', ['at' => 'one']);
    }

    function it_matches_that_json_path_not_exists()
    {
	$this->setSubject('{}')->shouldNotThrow()->duringNotHasPath('not_existing');
    }
    //</editor-fold>

    // <editor-fold desc="hasSize spec">
    function it_counts_array_entries()
    {
        $this->setSubject(('[1,2,3]'))->shouldNotThrow()->duringHasSize(3);
    }

    function it_counts_null_array_entries()
    {
        $this->setSubject(('[1,null,3]'))->shouldNotThrow()->duringHasSize(3);
    }

    function it_counts_hash_key_value_pairs()
    {
        $this->setSubject(('{"one":1,"two":2,"three":3}'))->shouldNotThrow()->duringHasSize(3);
    }

    function it_counts_null_hash_values()
    {
        $this->setSubject(('{"one":1,"two":null,"three":3}'))->shouldNotThrow()->duringHasSize(3);
    }

    function it_matches_size_at_a_path()
    {
        $this->setSubject(('{"one":[1,2,3]}'))->shouldNotThrow()->duringHasSize(3, ['at' => 'one']);
    }
    
    function it_fails_on_wrong_ammount_of_items()
    {
        $this->setSubject(('[1,null]'))->shouldThrow()->duringHasSize(3);
    }
    
    function it_matches_size_in_nagative_scenarios()
    {
        $this->setSubject(('[1,null]'))->shouldNotThrow()->duringNotHasSize(3);
    }

    function it_cant_match_size_of_scalars()
    {
        $this->setSubject(('{"one":[1,2,3]}'))
            ->shouldThrow(self::$jsonSizeException)
            ->duringHasSize(3, ['at' => 'one/0'])
        ;
    }
    //</editor-fold>

    // <editor-fold desc="hasType spec">
    function it_matches_objects()
    {
        $this->setSubject(('{}'))->shouldNotThrow()->duringHasType('object');
    }

    function it_matches_arrays()
    {
        $this->setSubject(('[]'))->shouldNotThrow()->duringHasType('array');
    }

    function it_matches_type_at_a_path()
    {
        $this->setSubject(('{"root":[]}'))
            ->shouldNotThrow()
            ->duringHasType('array', [
                'at' => 'root'
            ])
        ;
    }

    function it_matches_strings()
    {
        $this->setSubject(('["json_spec"]'))->shouldNotThrow()->duringHasType('string', ['at' => '0']);
    }

    function it_matches_a_valid_JSON_value_yet_invalid_JSON_document()
    {
        $this->setSubject(('"json_spec"'))->shouldNotThrow()->duringHasType('string');
    }

    function it_matches_empty_strings()
    {
        $this->setSubject(('""'))->shouldNotThrow()->duringHasType('string');
    }

    function it_matches_integers()
    {
        $this->setSubject(('10'))->shouldNotThrow()->duringHasType('integer');
    }

    function it_matches_floats()
    {
        $this->setSubject(('10.0'))->shouldNotThrow()->duringHasType('float');
        $this->setSubject(('1e+1'))->shouldNotThrow()->duringHasType('float');
    }

    function it_matches_booleans()
    {
        $this->setSubject(('true'))->shouldNotThrow()->duringHasType('boolean');
        $this->setSubject(('false'))->shouldNotThrow()->duringHasType('boolean');
    }
    //</editor-fold>

    // <editor-fold desc="includes spec">
    function it_matches_included_array_elements()
    {
        $json = '["one",1,1.0,true,false,null]';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('"one"');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('1');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('1.0');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('true');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('false');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('null');
    }

    function it_matches_an_array_included_in_an_array()
    {
        $json = '[[1,2,3],[4,5,6]]';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('[1, 2, 3]');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('[4, 5, 6]');
    }

    function it_matches_a_hash_included_in_an_array()
    {
        $json = '[{"one":1},{"two":2}]';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('{"one":1}');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('{"two":2}');
    }

    function it_matches_included_hash_values()
    {
        $json = '{"string":"one","integer":1,"float":1.0,"true":true,"false":false,"null":null}';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('"one"');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('1');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('1.0');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('true');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('false');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('null');
    }

    function it_matches_a_hash_included_in_a_hash()
    {
        $json = '{"one":{"two":3},"four":{"five":6}}';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('{"two":3}');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('{"five":6}');
    }

    function it_matches_an_array_included_in_a_hash()
    {
        $json = '{"one":[2,3],"four":[5,6]}';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('[2,3]');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('[5,6]');
    }

    function it_matches_a_substring()
    {
        $json = '"json"';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('"js"');
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('"json"');
    }

    function it_matches_t_a_path()
    {
        $json = '{"one":{"two":[3,4]}}';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('[3,4]', ['at' => 'one']);
    }

    function it_ignores_excluded_keys()
    {
        $json = '[{"id":1,"two":3}]';
        $this->setSubject(($json))->shouldNotThrow()->duringIncludes('{"two":3}');
    }

    function it_matches_an_subset_included_in_a_hash()
    {
        $json = '{"id": 1, "name": "Foo"}';
        $this->setSubject($json)->shouldNotThrow()->duringIncludes('{"name":"Foo"}');
    }

    function it_matches_an_subset_included_in_a_collection_of_hashes()
    {
        $json = '[{"id": 1, "name": "Foo"}, {"id": 2, "name": "Bar"}]';
        $this->setSubject($json)->shouldNotThrow()->duringIncludes('{"name":"Bar"}');
    }

    function it_should_throw_exception_if_it_cant_find_subset_in_hash()
    {
        $json = '{"id": 1, "name": "Foo"}';
        $this->setSubject($json)->shouldThrow()->duringIncludes('{"name":"Bar"}');
    }
    // </editor-fold>

}
