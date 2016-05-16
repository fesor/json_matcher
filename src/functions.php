<?php

namespace Fesor\JsonMatcher;

function json($json, array $excludedKeys = [])
{
    return JsonMatcher::create($json, $excludedKeys);
}

function at_path($path)
{
    return [];
}

/**
 * This is small shortcut for creating matching options
 *
 * @param string $expectedJson
 * @return \Fesor\JsonMatcher\JsonMatcherOptions
 */
function expectedJson($expectedJson = null) {
    return new \Fesor\JsonMatcher\JsonMatcherOptions();
}

function expectedSchema($schema) {
    return new \Fesor\JsonMatcher\JsonMatcherOptions($schema);
}

