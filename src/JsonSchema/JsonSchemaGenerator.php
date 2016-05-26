<?php

namespace Fesor\JsonMatcher\JsonSchema;

class JsonSchemaGenerator
{
    public function generateSchema($definition)
    {
        $schema = $this->processTypeDeclaration($definition);

        $schema['$schema'] = 'http://json-schema.org/draft-04/schema#';

        return json_encode($schema, JSON_PRETTY_PRINT);
    }

    private function processTypeDeclaration($definition)
    {
        if (is_string($definition)) {
            $definition = $this->parseTypeExpression($definition);
        }

        if (!isset($definition['type']) && !isset($definition['oneOf'])) {
            $definition['type'] = $this->guessType($definition);
        }

        if (isset($definition['oneOf'])) {
            $definition['oneOf'] = array_map(function ($type) {
                return $this->processTypeDeclaration($type);
            }, $definition['oneOf']);

            return $definition;
        }

        if ('array' === $definition['type'] && isset($definition['items'])) {
            $definition['items'] = $this->processTypeDeclaration($definition['items']);
        }

        if ('object' === $definition['type'] && isset($definition['properties'])) {
            return $this->processObjectType($definition);
        }

        return $definition;
    }

    private function guessType($definition)
    {
        $typeBasedOnPropsMap = [
            'properties' => 'object',
            'item' => 'array'
        ];

        foreach ($typeBasedOnPropsMap as $prop => $type) {
            if (isset($definition[$prop])) {
                return $type;
            }
        }

        return 'string';
    }

    private function processObjectType(array $obj)
    {
        if (!isset($obj['properties'])) {
            $obj['properties'] = [];
        }

        $obj['properties'] = $this->expandProperties($obj['properties']);
        // todo: support pattern properties
        $obj['required'] = array_keys(array_filter($obj['properties'], function ($type) {
            return array_key_exists('_required', $type) ? $type['_required'] : true;
        }));

        return $this->stripInternalRequiredKey($obj);
    }

    private function expandProperties($properties)
    {
        foreach ($properties as $prop => $definition) {
            $properties[$prop] = $this->processTypeDeclaration($properties[$prop]);

            if (array_key_exists('_required', $properties[$prop])) {
                continue;
            }

            if (mb_substr($prop, -1) === '?') {
                $expandedProp = mb_substr($prop, 0, -1);
                $properties[$expandedProp] = $properties[$prop];
                $properties[$expandedProp]['_required'] = false;
                unset($properties[$prop]);
            }
        }

        return $properties;
    }

    private function stripInternalRequiredKey(array $type)
    {
        if ('object' === $type['type']) {
            $type['properties'] = array_map(function ($type) {
                return $this->stripInternalRequiredKey($type);
            }, $type['properties']);
        }

        if ('array' === $type['type'] && isset($type['items'])) {
            $type['items'] = $this->stripInternalRequiredKey($type['items']);
        }

        if (array_key_exists('_required', $type)) {
            unset($type['_required']);
        }

        return $type;
    }

    function parseTypeExpression($type)
    {
        $rpn = $this->rpn($type);

        $stack = [];
        foreach ($rpn as $token) {
            switch ($token) {
                case '|':
                    $operands = [array_pop($stack), array_pop($stack)];;
                    if (isset($operands[0]['oneOf'])) {
                        $operands[0]['oneOf'][] = $operands[1];
                        $union = $operands[0];
                    } else {
                        $union = [
                            'oneOf' => $operands
                        ];
                    }
                    $union['oneOf'] = array_unique($union['oneOf'], SORT_REGULAR);
                    $stack[] = $union;
                    break;

                case '?':
                    $stack[] = [
                        'oneOf' => [
                            ['type' => 'null'],
                            array_pop($stack)
                        ]
                    ];
                    break;
                case '[]':
                    $stack[] = [
                        'type' => 'array',
                        'items' => array_pop($stack)
                    ];
                    break;
                default:
                    $stack[] = ['type' => $token];
                    break;
            }
        }

        return $stack[0];
    }

    private function rpn($str)
    {
        preg_match_all('/((\[\]){1}|[\(\)\|\?]|[^\(\)\|\?\[\]\s]+?)/U', $str, $matches);
        $tokens = $matches[0];
        $stack = [];
        $out = [];
        $operators = array_flip(['|', '[]', '?']);

        foreach ($tokens as $token) {
            switch ($token) {
                case '|':
                case '?':
                case '[]':
                    while (!empty($stack) && array_key_exists(end($stack), $operators)) {
                        if ($operators[$token] < $operators[end($stack)]) {
                            $out[] = array_pop($stack);
                            continue;
                        }
                        break;
                    }
                    $stack[] = $token;
                    break;
                case '(':
                    $stack[] = $token;
                    break;
                case ')':
                    while (!empty($stack) && end($stack) !== '(') {
                        $out[] = array_pop($stack);
                    }
                    array_pop($stack);
                    break;
                default:
                    $out[] = $token;
                    break;
            }
        }

        return array_merge($out, array_reverse($stack));
    }

}