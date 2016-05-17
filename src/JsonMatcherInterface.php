<?php
namespace Fesor\JsonMatcher;


/**
 * Class JsonMatcher
 * @package Fesor\JsonMatcher
 */
interface JsonMatcherInterface
{
    /**
     * Checks is given JSON equal to another one
     *
     * @param  string $expected
     * @param  array $options
     * @return $this
     */
    public function equal($expected, array $options = []);

    /**
     * Checks that given path exists in JSON
     *
     * @param  string|null $path
     * @param  array $options
     * @return $this
     */
    public function hasPath($path, array $options = []);

    /**
     * Checks that given JSON have exact amount of items
     *
     * @param  integer $expectedSize
     * @param  array $options
     * @return $this
     */
    public function hasSize($expectedSize, array $options = []);

    /**
     * Checks that given JSON at specific path have expected path
     *
     * @param  string $type
     * @param  array $options
     * @return $this
     */
    public function hasType($type, array $options = []);

    /**
     * Checks that given JSON presents in some collection or property
     *
     * @param  string $json
     * @param  array $options
     * @return $this
     */
    public function includes($json, array $options = []);
}