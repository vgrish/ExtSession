<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

/** @var  MODX\Revolution\modX $modx */

use MODX\Revolution\modSession;
use MODX\Revolution\modSystemSetting;

if ($transport->xpdo) {
    $modx = $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            $modx->query("TRUNCATE TABLE {$modx->getTableName(modSession::class)}");

            $modx->addPackage('ExtSession\Model', MODX_CORE_PATH . 'components/extsession/src/', null, 'ExtSession\\');
            $manager = $modx->getManager();
            $objects = [];
            $schemaFields = [];
            $schemaFile = MODX_CORE_PATH . 'components/extsession/schema/extsession.mysql.schema.xml';
            if (is_file($schemaFile)) {
                $schema = new SimpleXMLElement($schemaFile, 0, true);
                if (isset($schema->object)) {
                    foreach ($schema->object as $obj) {
                        $class = 'ExtSession\\Model\\' . (string)$obj['class'];
                        $objects[] = $class;
                        $schemaFields[$class] = [];
                        foreach ($obj->children() as $name => $field) {
                            if ($name === 'field' && $field->attributes()->key) {
                                $schemaFields[$class][] = (string)$field->attributes()->key;
                            }
                        }
                    }
                }
                unset($schema);
            }

            foreach ($objects as $class) {
                $table = $modx->getTableName($class);
                $sql = "SHOW TABLES LIKE '" . trim($table, '`') . "'";
                $stmt = $modx->prepare($sql);
                $newTable = true;
                if ($stmt->execute() && $stmt->fetchAll()) {
                    $newTable = false;
                }
                // If the table is just created
                if ($newTable) {
                    $manager->createObjectContainer($class);
                } else {
                    // If the table exists
                    // 1. Operate with tables
                    $tableFields = [];
                    $c = $modx->prepare("SHOW COLUMNS IN {$modx->getTableName($class)}");
                    $c->execute();
                    while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
                        $tableFields[$cl['Field']] = $cl['Field'];
                    }

                    if (!empty($schemaFields[$class])) {
                        foreach ($schemaFields[$class] as $idx => $field) {
                            if (in_array($field, $tableFields)) {
                                unset($tableFields[$field]);
                                if (isset($schemaFields[$class][$idx - 1])) {
                                    $manager->alterField($class, $field, ['after' => $schemaFields[$class][$idx - 1]]);
                                } else {
                                    $manager->alterField($class, $field);
                                }
                            } else {
                                $manager->addField($class, $field);
                            }
                        }
                    } else {
                        foreach ($modx->getFields($class) as $field => $v) {
                            if (in_array($field, $tableFields)) {
                                unset($tableFields[$field]);
                                $manager->alterField($class, $field);
                            } else {
                                $manager->addField($class, $field);
                            }
                        }
                    }

                    foreach ($tableFields as $field) {
                        $manager->removeField($class, $field);
                    }
                    // 2. Operate with indexes
                    $indexes = [];
                    $c = $modx->prepare("SHOW INDEX FROM {$modx->getTableName($class)}");
                    $c->execute();
                    while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
                        $name = $row['Key_name'];
                        if (!isset($indexes[$name])) {
                            $indexes[$name] = [$row['Column_name']];
                        } else {
                            $indexes[$name][] = $row['Column_name'];
                        }
                    }
                    foreach ($indexes as $name => $values) {
                        sort($values);
                        $indexes[$name] = implode(':', $values);
                    }
                    $map = $modx->getIndexMeta($class);
                    // Remove old indexes
                    foreach ($indexes as $key => $index) {
                        if (!isset($map[$key])) {
                            if ($manager->removeIndex($class, $key)) {
                                $modx->log(modX::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
                            }
                        }
                    }
                    // Add or alter existing
                    foreach ($map as $key => $index) {
                        ksort($index['columns']);
                        $index = implode(':', array_keys($index['columns']));
                        if (!isset($indexes[$key])) {
                            if ($manager->addIndex($class, $key)) {
                                $modx->log(modX::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
                            }
                        } else {
                            if ($index != $indexes[$key]) {
                                if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                    $modx->log(
                                        modX::LOG_LEVEL_INFO,
                                        "Updated index \"{$key}\" of the table \"{$class}\""
                                    );
                                }
                            }
                        }
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            /** @var $setting modSystemSetting */
            if ($setting = $modx->getObject(modSystemSetting::class, ['key' => 'session_handler_class', 'value' => 'ExtSession\ExtSessionHandler'])) {
                $setting->set('value', 'MODX\Revolution\modSessionHandler');
                $setting->save();
            }
            break;
    }
}

return true;
