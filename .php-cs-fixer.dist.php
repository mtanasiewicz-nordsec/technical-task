<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src/',
        __DIR__ . '/tests/',
    ]);;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'yoda_style' => false,
        'single_quote' => true,
        'strict_comparison' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_trim' => true,
        'no_unused_imports' => true,
        'mb_str_functions' => true,
        'array_syntax' => ['syntax' => 'short'],
        'whitespace_after_comma_in_array' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_empty_comment' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
                'case' => 'one',
            ]
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => [
                'const',
                'class',
                'function',
            ]
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ]
    ])
    ->setFinder($finder);
