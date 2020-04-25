<?php

namespace spec\Fesor\JsonMatcher\Helper;

use Fesor\JsonMatcher\Exception\MissingPathException;
use PhpSpec\ObjectBehavior;

class JsonHelperSpec extends ObjectBehavior
{
    public function it_parses_json()
    {
        $result = new \stdClass();
        $result->json = ['spec'];
        $this->parse('{"json":["spec"]}')->shouldBeLike($result);
    }

    public function it_parses_JSON_values()
    {
        $this->parse('"json_spec"')->shouldBe('json_spec');
        $this->parse('10')->shouldBe(10);
        $this->parse('null')->shouldBe(null);
    }

    public function it_raises_a_parser_error_for_invalid_JSON()
    {
        $this->shouldThrow()->duringParse('json_spec');
    }

    public function it_parses_at_a_path_if_given()
    {
        $json = '{"json": ["spec"]}';
        $this->parse($json, 'json')->shouldBeLike(['spec']);
        $this->parse($json, 'json/0')->shouldBe('spec');
    }

    public function it_raises_an_error_for_a_missing_path()
    {
        $json = '{"json": ["spec"]}';
        $this->shouldThrow(
            new MissingPathException('json/1')
        )->duringParse($json, 'json/1');
    }

    public function it_parses_at_a_numeric_string_path()
    {
        $json = '{"1": "json"}';
        $this->parse($json, '1')->shouldBe('json');
    }

    public function it_correctly_validate_json_value()
    {
        $this->isValid('"json_spec"')->shouldBe(true);
        $this->isValid('json_spec')->shouldBe(false);
    }

    public function it_normalize_json()
    {
        $normalizedJson = <<<JSON
{
    "json": "spec",
    "laser": {
        "banana": "watermelon",
        "lemon": "orange"
    }
}
JSON;

        $this->normalize('{"laser":{"lemon": "orange", "banana": "watermelon"},"json":"spec"}')->shouldBe(rtrim($normalizedJson));
    }

    public function it_normalize_json_value()
    {
        $this->normalize('1e+1')->shouldBe('10');
    }

    public function it_normalizes_at_a_path()
    {
        $this->normalize('{"json":["spec"]}', 'json/0')->shouldBe('"spec"');
    }

    public function it_accept_a_json_value()
    {
        $this->normalize('1e+1')->shouldBe('10');
    }

    public function it_normalizes_a_json_value()
    {
        $this->normalize('"json_spec"')->shouldBe('"json_spec"');
    }

    public function it_does_not_change_collection_order()
    {
        $normalizedJson = <<<JSON
[
    "spec",
    "json"
]
JSON;

        $this->generateNormalizedJson(['spec', 'json'])->shouldBe(rtrim($normalizedJson));
    }

    public function it_generates_a_normalized_json_document()
    {
        $normalizedJson = <<<JSON
{
    "json": [
        "spec"
    ]
}
JSON;
        $this->generateNormalizedJson((object) ['json' => ['spec']])->shouldBe(rtrim($normalizedJson));
    }

    public function it_should_exclude_keys()
    {
        $data = (object) [
            'id' => 1,
            'collection' => [
                (object) [
                    'id' => 1,
                    'json' => 'spec',
                ],
            ],
        ];

        $this->excludeKeys($data, ['id'])->shouldBeLike((object) [
            'collection' => [
                (object) [
                    'json' => 'spec',
                ],
            ],
        ]);
    }

    public function it_checks_is_collection_includes_json()
    {
        $this->isIncludes(['json'], '"json"')->shouldBe(true);
        $this->isIncludes(['spec'], '"json"')->shouldBe(false);
    }

    public function it_checks_is_json_string_includes_another_json_string()
    {
        $this->isIncludes('json', '"json"')->shouldBe(true);
        $this->isIncludes('json_spec', '"json"')->shouldBe(true);
        $this->isIncludes('spec', '"json"')->shouldBe(false);
    }

    public function it_checks_is_json_contains_in_some_property()
    {
        $obj = (object) [
            'test' => (object) [
                'key' => 'value',
            ],
        ];

        $needle = <<<JSON
{
    "key": "value"
}
JSON;
        $falseNeedle = <<<JSON
{
    "value": "key"
}
JSON;

        $this->isIncludes($obj, $needle)->shouldBe(true);
        $this->isIncludes($obj, $falseNeedle)->shouldBe(false);
    }

    public function it_checks_for_inclusions_recursively()
    {
        $obj = (object) [
            'test' => (object) [
                'key' => ['value', 'find me'],
            ],
        ];

        $this->isIncludes($obj, '"find"')->shouldBe(true);
        $this->isIncludes($obj, '"find me"')->shouldBe(true);
        $this->isIncludes($obj, '"not find me"')->shouldBe(false);
    }
}
