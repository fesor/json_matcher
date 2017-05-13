<?php

namespace Fesor\JsonMatcher;

use Fesor\JsonMatcher\Exception\MissingPathException;

/**
 * Class JsonHelper.
 */
class JsonHelper
{
    const NORMALIZED_JSON_OPTIONS = JSON_PRETTY_PRINT;

    /**
     * Returns parsed JSON data or its part by given path.
     *
     * @param string      $json
     * @param string|null $path
     *
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

    public function stringify($data)
    {
        return rtrim(json_encode($data, self::NORMALIZED_JSON_OPTIONS));
    }

    /**
     * Checks is given JSON string is valid or not.
     *
     * @param string $json
     *
     * @return bool
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
     * @param $json
     * @param null $path
     *
     * @return string
     */
    public function normalize($json, $path = null)
    {
        return $this->generateNormalizedJson($this->parse($json, $path));
    }

    public function normalizeAndParse($json, $path = null)
    {
        $data = $this->parse($json, $path);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function generateNormalizedJson($data)
    {
        return $this->stringify($this->sortObjectKeys($data));
    }

    public function excludeKeysFromJson(string $json, array $excludedKeys = [])
    {
        return $this->stringify($this->excludeKeys($this->parse($json), $excludedKeys));
    }

    /**
     * Recursively removes specific keys from.
     *
     * @param $data
     * @param array|null excludedKeys
     *
     * @return mixed
     */
    public function excludeKeys($data, array $excludedKeys = [])
    {
        if (is_object($data)) {
            $object = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                if (in_array($key, $excludedKeys)) {
                    continue;
                }
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
     * Get data by given JSON path.
     *
     * @param mixed  $data
     * @param string $path
     *
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
     * Recursively sorts objects keys.
     *
     * @param $data
     *
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
     *
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
