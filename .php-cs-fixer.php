<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__.'/src/');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])->setFinder($finder);
