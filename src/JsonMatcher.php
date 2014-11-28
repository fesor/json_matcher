<?php

namespace Fesor\JsonMatcher;

use Fesor\JsonMatcher\Exception\MissingPathException;
use Fesor\JsonMatcher\Helper\JsonHelper;

class JsonMatcher
{

    const OPTION_PATH = 'at';
    const OPTION_EXCLUDE_KEYS = 'excluding';
    const OPTION_INCLUDE_KEYS = 'including';

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
     * @param  string $expected
     * @param  array  $options
     * @return bool
     */
    public function equal($expected, array $options = [])
    {
        $actual = $this->scrub($this->subject, $options);
        $expected = $this->scrub($expected, array_diff_key(
            // we should pass all options except `path`
            $options, [static::OPTION_PATH => null]
        ));

        return $actual === $expected;
    }

    /**
     * @param  string|null $path
     * @param  array       $options
     * @return bool
     */
    public function havePath($path, array $options = [])
    {
        // get base path
        $basePath = $this->getPath($options);
        $path = ltrim($basePath . '/' . $path, '/');

        try {
            $this->jsonHelper->parse($this->subject, $path);
        } catch (MissingPathException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param  integer $expectedSize
     * @param  array   $options
     * @return bool
     */
    public function haveSize($expectedSize, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if (!is_array($data) && !is_object($data)) {
            return false;
        }

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        return $expectedSize === count($data);
    }

    /**
     * @param  string $type
     * @param  array $options
     * @return bool
     */
    public function haveType($type, array $options = [])
    {
        $data = $this->jsonHelper->parse($this->subject, $this->getPath($options));

        if ($type == 'float') {
            $type = 'double';
        }

        return gettype($data) === $type;
    }

    /**
     * @param  string $json
     * @param  array $options
     * @return bool
     */
    public function includes($json, array $options = [])
    {
        $actual = $this->scrub($this->subject, $options);
        $expected = $this->scrub($json, array_diff_key(
            // we should pass all options except `path`
            $options, [static::OPTION_PATH => null]
        ));

        return $this->jsonHelper->isIncludes($this->jsonHelper->parse($actual), $expected);
    }

    public function __invoke($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param $name
     * @param  array $arguments
     * @return bool
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
            throw new \RuntimeException('Matcher requires one argument');
        }

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

}
