<?php

namespace Fesor\JsonMatcher;

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
