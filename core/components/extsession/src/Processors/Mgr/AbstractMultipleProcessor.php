<?php
/**
 * Abstract Multiple processor
 *
 * @package extsession
 * @subpackage processors
 */

namespace ExtSession\Processors\Mgr;

use MODX\Revolution\modX;
use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Processors\ProcessorResponse;
use ExtSession\ExtSession;
use ExtSession\ExtSessionConfig;

/**
 * Class MultipleProcessor
 */
abstract class AbstractMultipleProcessor extends Processor
{
    public $languageTopics = ['extsession:default'];
    public $permission = 'edit';

    /** @var ExtSession $extsession */
    public $extsession;

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

    public function process()
    {
        if (!$method = $this->getProperty('method', false)) {
            return $this->failure();
        }
        $ids = json_decode($this->getProperty('ids'), true);

        if (!empty($ids)) {
            foreach ($ids as $id) {
                if ($id === '') {
                    continue;
                }
                /** @var ProcessorResponse $response */
                $response = $this->modx->runProcessor(ExtSessionConfig::PROCESSORS_ACTION_PREFIX . 'Mgr\\' . $method, [
                    $this->primaryKeyField => $id,
                    'field_name' => $this->getProperty('field_name', null),
                    'field_value' => $this->getProperty('field_value', null),
                ]);
                if ($response->isError()) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, $response->getMessage() . print_r($this->getProperties(), true));
                    //return $response->getResponse();
                }
            }
        } elseif ($this->getProperty('field_name') == 'false') {
            /** @var ProcessorResponse $response */
            $response = $this->modx->runProcessor(ExtSessionConfig::PROCESSORS_ACTION_PREFIX . 'Mgr\\' . $method);
            if ($response->isError()) {
                return $response->getResponse();
            }
        }

        return $this->success();
    }
}