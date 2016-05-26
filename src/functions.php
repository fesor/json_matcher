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

