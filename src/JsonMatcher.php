<?php

namespace Fesor\JsonMatcher;

use Fesor\JsonMatcher\Exception\JsonEqualityException;
use Fesor\JsonMatcher\Exception\JsonIncludesException;
use Fesor\JsonMatcher\Exception\JsonSizeException;
use Fesor\JsonMatcher\Exception\JsonTypeException;
use Fesor\JsonMatcher\Exception\MissingPathException;
use Fesor\JsonMatcher\Exception\PathMatchException;
use Fesor\JsonMatcher\Helper\JsonHelper;

/**
 * Class JsonMatcher
 * @package Fesor\JsonMatcher
 */
class JsonMatcher implements JsonMatcherInterface
{

    const OPTION_PATH = 'at';
    const OPTION_EXCLUDE_KEYS = 'excluding';
    const OPTION_INCLUDE_KEYS = 'including';
    const OPTION_NEGATIVE = '_negative';

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var array
     */
    private $excludeKeys;

    /**
     * @var string
     */
    private $subject;

    /**
     * @param JsonHelper $jsonHelper
     * @param array      $excludeKeys
     */
    public function __construct(JsonHelper $jsonHelper, array $excludeKeys = [])
    {
        $this->jsonHelper = $jsonHelper;
        $this->excludeKeys = $excludeKeys;
    }

    /**
     * Named constructor for simplify usage
     *
     * @param  string      $subject
     * @param  array       $excludedKeys
     * @return JsonMatcher
     */
    public static function create($subject, array $excludedKeys = ['id'])
    {
        $matcher = new JsonMatcher(new JsonHelper(), $excludedKeys);
        $matcher->setSubject($subject);
        
        return $matcher;
    }

    public function not()
    {
        return new InvertedJsonMatcher($this);
    }

    /**
     * Checks is given JSON equal to another one
     *
     * @param  string $expected
     * @param  array  $options
     * @return $this
     */
    public function equal($expected, array $options = [])
    {
        $actual = $this->scrub($this->subject, $options);
        $expected = $this->scrub($expected, array_diff_key(
            // we should pass all options except `path`
            $options, [static::OPTION_PATH => null]
        ));

        if ($this->isPositive($options) ^ $actual === $expected) {
            throw JsonEqualityException::create($options);
        }

        return $this;
    }

    /**
     * Checks that given path exists in JSON
     *
     * @param  string|null $path
     * @param  array       $options
     * @return $this
     */


    public function hasPath($path, array $options = [])
    {
        // get base path
        $basePath = $this->getPath($options);
        $path = ltrim($basePath . '/' . $path, '/');
        $pathExists = true;
        try {
            $this->jsonHelper->parse($this->subject, $path);
        } catch(MissingPathException $e){
            $pathExists = false;

        }

        if ($this->isPositive($options) ^ $pathExists) {
            throw new PathMatchException($path, $options);
        }

        return $this;
    }

    /**
     * Checks that given JSON have exact amount of items
     *
     * @param  integer $expectedSize
     * @param  array   $options
     * @return $this
     */
    public function hasSize($expectedSize, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (!(is_array($data) || is_string($data))) {

            throw new JsonSizeException('Can\'t get size of scalar JSON value');
        }

        if ($this->isPositive($options) ^ $expectedSize === count($data)) {
            throw JsonSizeException::create($expectedSize, count($data), $options);
        }

        return $this;
    }

    /**
     * Checks that given JSON at specific path have expected path
     *
     * @param  string $type
     * @param  array  $options
     * @return $this
     */
    public function hasType($type, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if ($type == 'float') {
            $type = 'double';
        }

        $actualType = gettype($data);
        if ($this->isPositive($options) ^ $actualType === $type) {
            throw JsonTypeException::create($type, $actualType, $options);
        }

        return $this;
    }

    /**
     * Checks that given JSON presents in some collection or property
     *
     * @param  string $json
     * @param  array  $options
     * @return $this
     */
    public function includes($json, array $options = [])
    {
        $actual = $this->scrub($this->subject, $options);
        $expected = $this->scrub($json, array_diff_key(
            // we should pass all options except `path`
            $options, [static::OPTION_PATH => null]
        ));

        if (
            $this->isPositive($options) ^ $this->jsonHelper->isIncludes(
                $this->jsonHelper->parse($actual), $expected
            )
        ) {
            throw JsonIncludesException::create($options);
        }

        return $this;
    }

    /**
     * Sets subject on which matching will be performed
     *
     * @param  string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Prepares JSON for matching
     *
     * @param  string $json
     * @param  array  $options
     * @return string
     */
    private function scrub($json, array $options = [])
    {
        return $this->jsonHelper->generateNormalizedJson(
            $this->jsonHelper->excludeKeys(
                $this->jsonHelper->parse($json, $this->getPath($options)),
                $this->getExcludedKeys($options)
            )
        );
    }

    /**
     * @param array $options
     * @return string|null
     */
    private function getPath(array $options)
    {
        return $this->option($options, static::OPTION_PATH, null);
    }

    /**
     * @param array $options
     * @return array
     */
    private function getExcludedKeys(array $options)
    {
        $excludedKeys = $this->option($options, static::OPTION_EXCLUDE_KEYS, []);
        $includedKeys = $this->option($options, static::OPTION_INCLUDE_KEYS, []);

        return array_diff(array_merge($this->excludeKeys, $excludedKeys), $includedKeys);
    }

    /**
     * @param array $options
     * @param string $optionName
     * @param mixed $default
     * @return mixed
     */
    private function option(array $options, $optionName, $default = null)
    {
        return array_key_exists($optionName, $options) ?
            $options[$optionName] : $default
        ;
    }

    /**
     * @param array $options
     * @return bool
     */
    private function isPositive(array $options)
    {
        return empty($options[self::OPTION_NEGATIVE]);
    }

}
