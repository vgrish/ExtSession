<?php

namespace ExtSession\Processors\Mgr\Traits;

use xPDO\Om\xPDOQuery;
use xPDO\Om\xPDOQueryCondition;

trait GetListTrait
{
    use BaseTrait;

    public function getTypesFields($className): array
    {
        $fields = [];
        if ($this->modx->map->offsetExists($className)) {
            if ($ancestry = $this->modx->getAncestry($className)) {
                for ($i = count($ancestry) - 1; $i >= 0; $i--) {
                    if ($tmp = $this->modx->map->offsetGet($ancestry[$i]) and isset($tmp['fieldMeta'])) {
                        $fields = array_merge($fields, $tmp['fieldMeta']);
                    }
                }
            }
            if ($this->modx->getInherit($className) === 'single') {
                $descendants = $this->modx->getDescendants($className);
                if ($descendants) {
                    foreach ($descendants as $descendant) {
                        if ($tmp = $this->modx->map->offsetGet($descendant) and isset($tmp['fieldMeta'])) {
                            $fields = array_merge($fields, array_diff_key($tmp['fieldMeta'], $fields));
                        }
                    }
                }
            }
        }
        return array_map(function ($row) {
            return $row['phptype'];
        }, $fields);
    }

    public function prepareQueryFilter(xPDOQuery $c)
    {
        $filter = $this->getJsonProperty('filter');
        if (empty($filter)) {
            return $c;
        }

        $sql = [];
        foreach ($filter as $row) {
            $field = $row['field'] ?? null;
            $value = $row['value'] ?? null;
            if (is_null($field) or is_null($value)) {
                continue;
            }

            if (empty($c->query['where'])) {
                $c->andCondition([
                    "{$this->getClassAlias()}.{$this->primaryKeyField}:!=" => 0,
                ], null, 1);
            }

            $ORvalues = explode('||', $value);     // Explode fields to array
            $ORvalues = array_map("trim", $ORvalues);       // Trim array"s values
            $ORvalues = array_keys(array_flip($ORvalues));  // Remove duplicate fields
            foreach ($ORvalues as $values) {

                $ANDvalues = explode('&&', $values);     // Explode fields to array
                $ANDvalues = array_map("trim", $ANDvalues);       // Trim array"s values
                $ANDvalues = array_keys(array_flip($ANDvalues));  // Remove duplicate fields

                $tmp = [];
                foreach ($ANDvalues as $value) {
                    switch (true) {
                        case strpos($value, '=') === 0:
                            $operator = '=';
                            $value = substr($value, 1);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        case strpos($value, '!') === 0:
                            $operator = 'NOT LIKE';
                            $value = substr($value, 1);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '%{$value}%' ESCAPE '=')";
                            break;
                        case strpos($value, '<>') === 0:
                            $operator = '<>';
                            $value = substr($value, 2);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        case strpos($value, '>=') === 0:
                            $operator = '>=';
                            $value = substr($value, 2);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        case strpos($value, '>') === 0:
                            $operator = '>';
                            $value = substr($value, 1);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        case strpos($value, '<=') === 0:
                            $operator = '<=';
                            $value = substr($value, 2);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        case strpos($value, '<') === 0:
                            $operator = '<';
                            $value = substr($value, 1);
                            $tmp[] = "({$this->getTableField($field)} {$operator} '{$value}')";
                            break;
                        default:
                            $operator = 'LIKE';
                            $tmp[] = "({$this->getTableField($field)} {$operator} '%{$value}%' ESCAPE '=')";
                            break;
                    }
                }
                $sql[$this->getClassAlias() . '_' . $field][] = implode(' AND ', $tmp);
            }
        }

        foreach ($sql as $v) {
            $c->query['where'][] = new xPDOQueryCondition([
                'sql' => '(' . implode(' OR ', $v) . ')',
                'conjunction' => "AND",
            ]);
        }

        return $c;
    }

}