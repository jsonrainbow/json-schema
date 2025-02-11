<?php

$finder = new PhpCsFixer\Finder();
$config = new PhpCsFixer\Config('json-schema');
$finder->in([__DIR__ . '/src', __DIR__ . '/tests']);

/* Based on ^2.1 of php-cs-fixer */
$config
    ->setRules([
        // default
        '@PSR2' => true,
        '@Symfony' => true,
        // additionally
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => false,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_no_package' => false,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'none', 'sort_algorithm' => 'none'],
        'simplified_null_return' => false,
        'single_line_throw' => false,
        'trailing_comma_in_multiline' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;

return $config;
