<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src/Controller',
        __DIR__.'/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
