<?php
namespace ExtSession\Model\mysql;

use xPDO\xPDO;

class Session extends \ExtSession\Model\Session
{

    public static $metaMap = array (
        'package' => 'ExtSession\\Model',
        'version' => '3.0',
        'extends' => 'MODX\\Revolution\\modSession',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'id' => '',
            'access' => NULL,
            'user_bot' => 0,
            'user_id' => 0,
            'user_ip' => '',
            'user_agent' => '',
            'data' => NULL,
        ),
        'fieldMeta' => 
        array (
            'id' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => false,
                'index' => 'pk',
                'default' => '',
            ),
            'access' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'timestamp',
                'null' => false,
                'attributes' => 'unsigned',
            ),
            'user_bot' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'attributes' => 'unsigned',
                'null' => false,
                'default' => 0,
            ),
            'user_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'integer',
                'null' => false,
                'attributes' => 'unsigned',
                'default' => 0,
            ),
            'user_ip' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '45',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'user_agent' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'data' => 
            array (
                'dbtype' => 'mediumtext',
                'phptype' => 'string',
            ),
        ),
        'indexes' => 
        array (
            'PRIMARY' => 
            array (
                'alias' => 'PRIMARY',
                'primary' => true,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'access' => 
            array (
                'alias' => 'access',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'access' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'user_bot' => 
            array (
                'alias' => 'user_bot',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'user_bot' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'user_id' => 
            array (
                'alias' => 'user_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'user_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'user_ip' => 
            array (
                'alias' => 'user_ip',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'user_ip' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'user_agent' => 
            array (
                'alias' => 'user_agent',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'user_agent' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'united' => 
            array (
                'alias' => 'united',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'access' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'user_bot' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'user_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'user_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Profile' => 
            array (
                'class' => 'MODX\\Revolution\\modUserProfile',
                'local' => 'user_id',
                'foreign' => 'internalKey',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
        'validation' => 
        array (
            'rules' => 
            array (
                'id' => 
                array (
                    'invalid' => 
                    array (
                        'type' => 'preg_match',
                        'rule' => '/^[0-9a-zA-Z,-]{22,191}$/',
                        'message' => 'session_err_invalid_id',
                    ),
                ),
            ),
        ),
    );

}
