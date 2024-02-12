<?php
/**
 * Abstract get list processor
 *
 * @package extsession
 * @subpackage processors
 */

namespace ExtSession\Processors\Mgr;

use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;
use xPDO\Om\xPDOQueryCondition;
use MODX\Revolution\modX;
use MODX\Revolution\Processors\Model\GetListProcessor;
use ExtSession\ExtSession;
use ExtSession\Processors\Mgr\Traits\GetListTrait;

/**
 * Class GetListProcessor
 */
abstract class AbstractGetListProcessor extends GetListProcessor
{
    use GetListTrait;

    public $languageTopics = ['extsession:default'];
    public $permission = 'list';

    /** @var ExtSession $extsession */
    public $extsession;

    public $classKey = '';
    public $classAlias = '';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = '';
    public $primaryKeyField = 'id';

    protected $typesFields = [];
    protected $prepareArrayByTypeField = true;
    protected $prepareArrayFields = [];
    protected $searchFields = [];

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);
        $this->extsession = $modx->services->get('extsession');
        $this->typesFields = array_merge($this->getTypesFields($this->classKey), $this->typesFields);
    }

    public function initialize()
    {
        $combo = $this->getBooleanProperty('combo', false);
        if ($combo) {
            $this->prepareArrayFields = $this->getJsonProperty('fields');
        }

        $this->setDefaultProperties([
            'start' => 0,
            'limit' => 20,
            'sort' => $this->defaultSortField,
            'dir' => $this->defaultSortDirection,
            'combo' => false,
            'query' => '',
        ]);

        $this->setProperty('combo', $combo);

        if (array_key_exists('rendered', $this->properties) and !filter_var($this->properties['rendered'], FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return parent::initialize();
    }


    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c = $this->prepareQueryFilter($c);
        $query = trim($this->getProperty('query', ''));
        if ($query !== '') {
            $conditions = [];
            $or = '';
            foreach ($this->searchFields as $field) {
                $conditions[$or . $field . ':LIKE'] = '%' . $query . '%';
                $or = 'OR:';
            }
            $c->where([$conditions]);
        }


        if ($this->getProperty('combo') and $pk = $this->getProperty($this->primaryKeyField)) {
            $c->where([$this->primaryKeyField => $pk]);
        }

        return $c;
    }

    /**
     * {@inheritDoc}
     * @param \xPDOQuery $c
     *
     * @return \xPDOQuery
     */
    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $q = clone $c;

        $total = 0;
        $limit = (int)$this->getProperty('limit');
        $start = (int)$this->getProperty('start');

        $sortKey = $this->getSortKey();
        $q->sortby($sortKey, $this->getProperty('dir'));
        $columns = ["`{$this->getClassAlias()}`.`{$this->primaryKeyField}` AS `{$this->getClassAlias()}.{$this->primaryKeyField}`"];
        if (!empty($sortKey)) {
            foreach ($q->query['columns'] as $column) {
                if (false !== strpos($column, $sortKey) and !in_array($column, $columns)) {
                    $columns[] = $column;
                }
            }
        }
        $q->query['columns'] = ["SQL_CALC_FOUND_ROWS " . implode(',', $columns)];
        $q->query['sortby'][] = [
            'column' => "`{$this->getClassAlias()}`.`{$this->primaryKeyField}`",
            'direction' => 'ASC',
        ];
        if ($limit > 0) {
            $q->limit($limit, $start);
        }
        $q->groupby("`{$this->getClassAlias()}`.`{$this->primaryKeyField}`");

        $ids = [];
        if ($q->prepare() and $q->stmt->execute()) {
            $ids = $q->stmt->fetchAll(\PDO::FETCH_COLUMN);
            $total = $this->modx->query('SELECT FOUND_ROWS()')->fetchColumn();
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $q->toSQL());
            $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($q->stmt->errorInfo(), 1));
        }

        if (!$total) {
            $ids = [0];
        }
        $sortIds = "'" . implode("','", array_reverse($ids)) . "'";

        $c->query['where'] = [
            [
                new xPDOQueryCondition(['sql' => "`{$this->getClassAlias()}`.`{$this->primaryKeyField}` IN ('" . implode("','", $ids) . "')", 'conjunction' => 'AND']),
            ],
        ];
        $c->query['sortby'] = [
            ['column' => "FIELD (`{$this->getClassAlias()}`.`{$this->primaryKeyField}`, {$sortIds})", 'direction' => 'DESC'],
        ];
        $c->groupby("`{$this->getClassAlias()}`.`{$this->primaryKeyField}`");
        $this->setProperty('total', $total);

        return $c;
    }

    public function getSortKey()
    {
        $sort = $this->getProperty('sort');
        if (false !== strpos($sort, '.')) {
            [$alias, $field] = explode('.', $sort);
        } else {
            $alias = $this->getClassAlias();
            $field = $sort;
        }

        return "`{$alias}`.`{$field}`";
    }

    public function getSortClassKey()
    {
        return $this->getClassAlias();
    }

    public function getTableField($key)
    {
        if (false !== strpos($key, '.')) {
            [$alias, $field] = explode('.', $key);
        } else {
            $alias = $this->getClassAlias();
            $field = $key;
        }

        return "`{$alias}`.`{$field}`";
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process()
    {
        $beforeQuery = $this->beforeQuery();
        if ($beforeQuery !== true) {
            return $this->failure($beforeQuery);
        }
        $data = $this->getData();
        $list = $this->iterate($data);
        return $this->outputData($list, $data);
    }

    /** {@inheritDoc} */
    public function getData()
    {
        $c = $this->modx->newQuery($this->classKey);
        $c->setClassAlias($this->getClassAlias());
        $c = $this->prepareQueryBeforeCount($c);
        $c = $this->prepareQueryAfterCount($c);

        $results = [];
        if ($c->prepare() and $c->stmt->execute()) {
            $results = $c->stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $c->toSQL());
            $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($c->stmt->errorInfo(), 1));
        }

        return $this->getResults([
            'results' => $results,
            'total' => (int)$this->getProperty('total'),
        ]);
    }

    public function getResults($results = [])
    {
        return $results;
    }

    public function outputData(array $array, array $data)
    {
        $count = $data['total'] ?? false;
        if ($count === false) {
            $count = count($array);
        }
        $output = json_encode([
            'success' => true,
            'total' => $count,
            'results' => $array,
            'data' => $data['data'] ?? [],
        ]);
        if ($output === false) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Processor failed creating output array due to JSON error ' . json_last_error());
            return json_encode(['success' => false]);
        }
        return $output;
    }

    /** {@inheritDoc} */
    public function getClassAlias()
    {
        return !empty($this->classAlias) ? $this->classAlias : $this->classKey;
    }


    /** {@inheritDoc} */
    public function iterate(array $data)
    {
        $list = [];
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;
        /** @var xPDOObject $object */
        foreach ($data['results'] as $row) {
            $list[] = $this->prepareArray($row);
            $this->currentIndex++;
        }
        $list = $this->afterIteration($list);

        return $list;
    }

    /** {@inheritDoc} */
    public function prepareArray(array $row, $toPls = true)
    {
        if (!empty($this->prepareArrayFields)) {
            $row = array_intersect_key($row, array_flip($this->prepareArrayFields));
        }

        if ($this->prepareArrayByTypeField) {
            foreach ($this->typesFields as $key => $phptype) {
                if (!array_key_exists($key, $row)) {
                    continue;
                }

                $v = $row[$key];
                switch ($phptype) {
                    case 'boolean' :
                        $v = (bool)$v;
                        break;
                    case 'integer' :
                        $v = (int)$v;
                        break;
                    case 'float' :
                        $v = (float)$v;
                        break;
                    case 'array' :
                        if (is_string($v)) {
                            $v = unserialize($v);
                        }
                        break;
                    case 'json' :
                        if (is_string($v)) {
                            $v = json_decode($v, true);
                        }
                        break;
                    case 'split' :
                        if (is_string($v)) {
                            $v = explode(',', $v);
                        }
                        break;
                    case 'string' :
                        if (is_array($v)) {
                            $v = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        }
                        $v = (string)$v;
                        if ($v != '') {
                            $v = str_replace(["\r\n", "\r", "\n"], ' ', $v);
                        }
                        break;
                    case 'datetime':
                        $v = (string)$v;
                        break;
                    case 'timestamp':
                        if ((int)$v) {
                            $v = date('Y-m-d H:i', (int)$v);
                        }
                        break;
                }

                $row[$key] = $v;
            }
        }

        if (!$toPls) {
            return $row;
        }

        $pls = [];
        foreach ($row as $k => $v) {
            if (false !== $d = strpos($k, '.')) {
                $kbefore = substr($k, 0, $d);
                $kafter = substr($k, 1 + $d);

                if (!isset($pls[$kbefore])) {
                    $pls[$kbefore] = [];
                }
                $pls[$kbefore][$kafter] = $v;
            } else {
                $pls[$k] = $v;
            }
        }

        return $pls;
    }

}