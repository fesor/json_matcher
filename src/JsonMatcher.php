<?php

namespace Fesor\JsonMatcher;

use Fesor\JsonMatcher\Exception\JsonEqualityException;
use Fesor\JsonMatcher\Exception\JsonIncludesException;
use Fesor\JsonMatcher\Exception\JsonSizeException;
use Fesor\JsonMatcher\Exception\JsonTypeException;
use Fesor\JsonMatcher\Helper\JsonHelper;
use Seld\JsonLint\JsonParser;

/**
 * Class JsonMatcher
 * @package Fesor\JsonMatcher
 *
 * @method $this notEqual() notEqual(string $expected, array $options=[])
 * @method $this notHaveSize() notHaveSize(int $expectedSize, array $options=[])
 * @method $this notHaveType() notHaveType(string $type, array $options=[])
 * @method $this notHavePath() notHavePath(string $path, array $options=[])
 * @method $this notIncludes() notIncludes(string $json, array $options=[])
 */
class JsonMatcher
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
        $matcher = new JsonMatcher(new JsonHelper(new JsonParser()), $excludedKeys);
        $matcher->setSubject($subject);
        
        return $matcher;
    }

    /**
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

        if (static::isPositive($options) xor $actual === $expected) {
            throw JsonEqualityException::create($options);
        }

        return $this;
    }

    /**
     * @param  string|null $path
     * @param  array       $options
     * @return $this
     */
    public function havePath($path, array $options = [])
    {
        // get base path
        $basePath = $this->getPath($options);
        $path = ltrim($basePath . '/' . $path, '/');

        $this->jsonHelper->parse($this->subject, $path);

        return $this;
    }

    /**
     * @param  integer $expectedSize
     * @param  array   $options
     * @return $this
     */
    public function haveSize($expectedSize, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (!(is_array($data) || is_string($data))) {

            throw new JsonSizeException('Can\'t get size of scalar JSON value');
        }

        if ($this->isPositive($options) xor $expectedSize === count($data)) {
            throw JsonSizeException::create($expectedSize, count($data), $options);
        }

        return $this;
    }

    /**
     * @param  string $type
     * @param  array  $options
     * @return $this
     */
    public function haveType($type, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if ($type == 'float') {
            $type = 'double';
        }

        $actualType = gettype($data);
        if ($this->isPositive($options) xor $actualType === $type) {
            throw JsonTypeException::create($type, $actualType, $options);
        }

        return $this;
    }

    /**
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
            $this->isPositive($options) xor $this->jsonHelper->isIncludes(
                $this->jsonHelper->parse($actual), $expected
            )
        ) {
            throw JsonIncludesException::create($options);
        }

        return $this;
    }

    /**
     * @param  string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param  string $name
     * @param  array  $arguments
     * @return $this
     */
    public function __call($name, array $arguments = [])
    {
        if (0 !== strpos($name, 'not')) {
            throw new \RuntimeException(sprintf('Method "%s" not exists', $name));
        }

        $matcher = lcfirst(substr($name, 3));
        if (!method_exists($this, $matcher)) {
            throw new \RuntimeException(sprintf('Matcher "%s" not supported', $matcher));
        }

        if (count($arguments) < 1) {
            throw new \RuntimeException('Matcher requires at least one argument');
        }

        $options = array_pop($arguments);
        if (!is_array($options)) {
            array_push($arguments, $options);
            $options = [];
        }

        $options[self::OPTION_NEGATIVE] = true;
        array_push($arguments, $options);

        return !call_user_func_array([$this, $matcher], $arguments);
    }

    /**
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

    private function getPath(array $options)
    {
        return $this->option($options, static::OPTION_PATH, null);
    }

    private function getExcludedKeys(array $options)
    {
        $excludedKeys = $this->option($options, static::OPTION_EXCLUDE_KEYS, []);
        $includedKeys = $this->option($options, static::OPTION_INCLUDE_KEYS, []);

        return array_diff(array_merge($this->excludeKeys, $excludedKeys), $includedKeys);
    }

    private function option(array $options, $optionName, $default = null)
    {
        return array_key_exists($optionName, $options) ?
            $options[$optionName] : $default
        ;
    }

    private function isPositive(array $options)
    {
        return empty($options[self::OPTION_NEGATIVE]);
    }

}
