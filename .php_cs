#!/usr/bin/env php
<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap')
    ->exclude('public')
    ->exclude('resources')
    ->notPath('server.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);