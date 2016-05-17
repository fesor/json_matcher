<?php

namespace Fesor\JsonMatcher;

use JsonSchema\RefResolver;
use JsonSchema\Uri\Retrievers\PredefinedArray;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

/**
 * Simplify usage of justinrainbow/json-schema
 *
 * @param string $schema
 * @return object
 */
function _resolveSchema($schema)
{
    $retriaver = new UriRetriever();
    $retriaver->setUriRetriever(new PredefinedArray([
        '' => $schema
    ]));

    $refResolver = new RefResolver(
        $retriaver,
        new UriResolver()
    );

    return $refResolver->resolve('#');
}

/**
 * @param $json
 * @param $schema
 * @return array
 */
function _validateJsonSchema($json, $schema)
{
    $data = json_decode($json);
    if (JSON_ERROR_NONE !== json_last_error()) {
        // fixme: handle invalid json cases
    }

    
}

function _parseTypeExpression($type)
{
    $rpn = _rpn($type);

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

function _rpn($str)
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
