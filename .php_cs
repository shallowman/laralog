#!/usr/bin/env php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'=> true,
        'array_syntax' => ['syntax' => 'short'],
    ])->setFinder($finder);