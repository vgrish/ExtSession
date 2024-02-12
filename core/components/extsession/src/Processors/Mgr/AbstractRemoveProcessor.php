<?php
/**
 * Abstract remove processor
 *
 * @package extsession
 * @subpackage processors
 */

namespace ExtSession\Processors\Mgr;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Model\RemoveProcessor;
use MODX\Revolution\modAccessibleObject;
use ExtSession\ExtSession;

/**
 * Class RemoveProcessor
 */
abstract class AbstractRemoveProcessor extends RemoveProcessor
{
    public $languageTopics = ['extsession:default'];
    public $permission = 'remove';

    /** @var ExtSession $extsession */
    public $extsession;

    public $classKey = '';

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);
        $this->extsession = $modx->services->get('extsession');
    }

    public function initialize()
    {
        $primaryKey = $this->getProperty($this->primaryKeyField);
        if ($primaryKey === '' or $primaryKey === null) return $this->modx->lexicon($this->objectType . '_err_ns1');
        $this->object = $this->modx->getObject($this->classKey, [$this->primaryKeyField => $primaryKey]);
        if (empty($this->object)) return $this->modx->lexicon($this->objectType . '_err_nfs', [$this->primaryKeyField => $primaryKey]);

        if ($this->checkRemovePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('remove')) {
            return $this->modx->lexicon('access_denied');
        }

        return parent::initialize();
    }

}