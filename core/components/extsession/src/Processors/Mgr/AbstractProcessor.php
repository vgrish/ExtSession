<?php


/**
 * Abstract processor
 *
 * @package extsession
 * @subpackage processors
 */

namespace ExtSession\Processors\Mgr;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Processor;
use ExtSession\ExtSession;

/**
 * Class Processor
 */
abstract class AbstractProcessor extends Processor
{
    public $languageTopics = ['extsession:default'];
    public $permission = '';

    /** @var ExtSession $extsession */
    public $extsession;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);
        $this->extsession = $modx->services->get('extsession');
    }

    public function checkPermissions()
    {
        return empty($this->permission) || $this->modx->hasPermission($this->permission);
    }

    public function process()
    {

    }
}