<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__.'/src/');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'single_line_empty_body' => true,
        'modernize_types_casting' => true,
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'logical_operators' => true,
    ])->setFinder($finder);
