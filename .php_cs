<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/spec')
    ->files()
    ->name('*.php');

$rules = [
    '@Symfony' => true,
    'blank_line_before_statement' => false,
    'phpdoc_types_order' => false,
    'increment_style' => false,
    'standardize_increment' => false,
    'array_syntax' => ['syntax' => 'short'],
    'no_superfluous_phpdoc_tags' => true,
    'no_superfluous_elseif' => true,
    'array_indentation' => true,
    'method_chaining_indentation' => true,
    'no_unused_imports' => true,
    'no_extra_consecutive_blank_lines' => ['use'],
    'php_unit_namespaced' => ['target' => 'newest'],
    'php_unit_expectation' => true,
    'concat_space' => false,
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setFinder($finder);
