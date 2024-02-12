<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

require_once __DIR__ . '/vendor/autoload.php';

$modx->addPackage('ExtSession\Model', $namespace['path'] . 'src/', null, 'ExtSession\\');
$modx->services->add('extsession', function ($c) use ($modx) {
    return new ExtSession\ExtSession($modx);
});