<?php

namespace Fesor\JsonMatcher\Helper;

use Fesor\JsonMatcher\Exception\MissingPathException;

/**
 * Class JsonHelper
 * @package Fesor\JsonMatcher\Helper
 *
 * Collection of utilities to work with JSON
 */
class JsonHelper
{

    /**
     * Returns parsed JSON data or its part by given path
     *
     * @param  string      $json
     * @param  string|null $path
     * @return mixed
     */
    public function parse($json, $path = null)
    {
        $data = $this->parseJson($json);

        if ($path === null) {
            
            return $data;
        }

        return $this->getAtPath($data, $path);
    }

    /**
     * Checks is given JSON string is valid or not
     *
     * @param  string  $json
     * @return boolean
     */
    public function isValid($json)
    {
        try {
            $this->parseJson($json);
        } catch (\InvalidArgumentException $e) {

            return false;
        }

        return true;
    }

    /**
     * Checks is given JSON contains somewhere in
     *
     * @param  mixed  $haystack contains parsed JSON value
     * @param  string $needle
     * @return bool
     */
    public function isIncludes($haystack, $needle)
    {

        $parsedJson = $this->parse($needle);
        $normalizedData = $this->generateNormalizedJson($haystack);
        if (!is_object($haystack) && !is_array($haystack)) {
            if (is_string($haystack) && is_string($parsedJson)) {
                return false !== strpos($haystack, $parsedJson);
            }
        }

        if ($normalizedData === $needle) {
            return true;
        }

        if (is_object($haystack)) {
            $haystack = get_object_vars($haystack);
        }

        if (is_array($haystack)) {
            foreach ($haystack as $value) {
                if ($this->isIncludes($value, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $json
     * @param  null   $path
     * @return string
     */
    public function normalize($json, $path = null)
    {
        return $this->generateNormalizedJson($this->parse($json, $path));
    }

    /**
     * @param  mixed  $data
     * @return string
     */
    public function generateNormalizedJson($data)
    {
        return rtrim(json_encode(
            $this->sortObjectKeys($data),
            JSON_PRETTY_PRINT
        ));
    }

    /**
     * Recursively removes specific keys from
     *
     * @param $data
     * @param array|null excludedKeys
     * @return mixed
     */
    public function excludeKeys($data, array $excludedKeys = array())
    {

        if (is_object($data)) {
            $object = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                if (in_array($key, $excludedKeys)) continue;
                $object->$key = $this->excludeKeys($value, $excludedKeys);
            }

            return $object;
        }

        if (is_array($data)) {
            return array_map(function ($data) use ($excludedKeys) {
                return $this->excludeKeys($data, $excludedKeys);
            }, $data);
        }

        return $data;
    }

    /**
     * Get data by given JSON path
     *
     * @param  mixed  $data
     * @param  string $path
     * @return mixed
     */
    private function getAtPath($data, $path)
    {
        $pathSegments = explode('/', trim($path, '/'));
        foreach ($pathSegments as $key) {

            if ($data instanceof \stdClass && property_exists($data, $key)) {
                $data = $data->$key;
            } elseif (is_array($data) && is_numeric($key) && array_key_exists((int) $key, $data)) {
                $data = $data[$key];
            } else {
                throw new MissingPathException($path);
            }
        }

        return $data;
    }

    /**
     * Recursively sorts objects keys
     *
     * @param $data
     * @return array|object
     */
    private function sortObjectKeys($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data;
        }

        $orderedData = $data;
        if (is_object($data)) {
            $orderedData = get_object_vars($data);
            ksort($orderedData);
        }

        foreach ($orderedData as &$value) {
            $value = $this->sortObjectKeys($value);
        }

        return is_object($data) ?
            (object) $orderedData : $orderedData;
    }

    /**
     * @param string $json
     * @return mixed
     */
    private function parseJson($json)
    {
        $json = json_decode($json);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Invalid JSON');
        }

        return $json;
    }
}
