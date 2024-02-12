<?php

/**
 * ExtSession connector
 *
 * @package extsession
 * @subpackage connector
 *
 * @var MODX\Revolution\modX $modx
 *
 */

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var MODX\Revolution\modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'action' => ExtSession\ExtSessionConfig::PROCESSORS_ACTION_PREFIX .'Mgr\\'. $_REQUEST['action'] ?? '',
    'processors_path' => ExtSession\ExtSessionConfig::PROCESSORS_PATH,
]);