<?php

namespace spec\Fesor\JsonMatcher\JsonSchema;

use Fesor\JsonMatcher\Exception\JsonEqualityException;
use Fesor\JsonMatcher\Exception\MissingPathException;
use Fesor\JsonMatcher\JsonMatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonSchemaGeneratorSpec extends ObjectBehavior
{
    function it_handles_type_declaration_as_string()
    {
        $this->generateSchema('string')->shouldReturnJsonSchema('{
            "type": "string"
        }');
    }

    function it_supports_nullable_type_expressions()
    {
        $this->generateSchema('string?')->shouldReturnJsonSchema('{
            "oneOf": [
                {"type": "null"},
                {"type": "string"}
            ]
        }');
    }

    function it_supports_array_type_expressions()
    {
        $this->generateSchema('string[]')->shouldReturnJsonSchema('{
            "type": "array",
            "items": {"type": "string"}
        }');
    }

    function it_supports_array_of_arrays_type_expressions()
    {
        $this->generateSchema('string[][]')->shouldReturnJsonSchema('{
            "type": "array",
            "items": {
               "type": "array",
               "items": {"type": "string"}
            }
        }');
    }

    function it_supports_union_types_in_type_expressions()
    {
        $this->generateSchema('string | number')->shouldReturnJsonSchema('{
            "oneOf": [
                {"type": "number"},
                {"type": "string"}
            ]
        }');
    }

    function it_supports_parenthesis_in_type_expressions()
    {
        $this->generateSchema('(string | number)[]')->shouldReturnJsonSchema('{
            "type": "array",
            "items": {
                "oneOf": [
                    {"type": "number"},
                    {"type": "string"}
                ]
            }
        }');
    }

    function it_supports_objects_declaration()
    {
        $this->generateSchema([
            'properties' => [
                'foo' => 'string',
                'bar?' => [
                    'properties' => [
                        'bar' => 'string'
                    ]
                ]
            ]
        ])->shouldReturnJsonSchema('{
            "type": "object",
            "properties": {
                "foo": {"type": "string"},
                "bar": {
                    "type": "object",
                    "properties": {
                        "bar": {"type": "string"}
                    },
                    "required": ["bar"]        
                }
            },
            "required": ["foo"]
        }');
    }

    public function getMatchers()
    {
        return [
            'returnJsonSchema' => function ($subject, $expectedJson) {

                $json = JsonMatcher::create($subject, ['$schema']);

                try {
                    $json->hasPath('$schema');
                } catch (MissingPathException $e) {
                    return false;
                }

                try {
                    $json->equal($expectedJson);
                } catch (JsonEqualityException $e) {
                    return false;
                }

                return true;
            }
        ];
    }
}
