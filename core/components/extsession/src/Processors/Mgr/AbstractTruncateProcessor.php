<?php
/**
 * Abstract Truncate processor
 *
 * @package extsession
 * @subpackage processors
 */

namespace ExtSession\Processors\Mgr;

/**
 * Class CreateProcessor
 */
abstract class AbstractTruncateProcessor extends AbstractProcessor
{

    public function process()
    {
        $this->truncateTable();
        return $this->success('');
    }

    protected function truncateTable()
    {
        if (!empty($this->classKey) and $table = $this->modx->getTableName($this->classKey)) {
            $this->modx->exec("TRUNCATE {$table};ALTER TABLE {$table} AUTO_INCREMENT = 0;");
        }
    }

}