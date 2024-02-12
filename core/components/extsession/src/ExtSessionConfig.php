<?php

namespace ExtSession;

class ExtSessionConfig
{
    public const VERSION = '1.0.0';
    public const NAMESPACE = 'extsession';
    public const CORE_PATH = MODX_CORE_PATH . 'components/extsession/src/';
    public const MODEL_PATH = MODX_CORE_PATH . 'components/extsession/src/Model/';
    public const PROCESSORS_PATH = MODX_CORE_PATH . 'components/extsession/src/Processors/';
    public const PROCESSORS_ACTION_PREFIX = 'ExtSession\\Processors\\';
    public const ASSETS_PATH = MODX_ASSETS_PATH . 'components/extsession/';
    public const ASSETS_URL = MODX_ASSETS_URL . 'components/extsession/';
    public const CONNECTOR_URL = MODX_ASSETS_URL . 'components/extsession/connector.php';
}