<?php

/*
 * session_handler_class
 * MODX\Revolution\modSessionHandler
 * ExtSession\ExtSessionHandler
 */

return [
    'bot_patterns' => [
        'value' => 'Yandex|Google|Yahoo|Rambler|Mail|Bot|Spider|Snoopy|Crawler|Finder|curl|Wget|Go-http-client',
        'xtype' => 'textfield',
        'area' => 'extsession_main',
    ],
    'bot_gc_maxlifetime' => [
        'value' => 3600,
        'xtype' => 'numberfield',
        'area' => 'extsession_main',
    ],
    'empty_user_id_gc_maxlifetime' => [
        'xtype' => 'numberfield',
        'value' => 86400,
        'area' => 'extsession_main',
    ],
    'not_empty_user_id_gc_maxlifetime' => [
        'xtype' => 'numberfield',
        'value' => 604800,
        'area' => 'extsession_main',
    ],
    'empty_user_agent_gc_maxlifetime' => [
        'xtype' => 'numberfield',
        'value' => 86400,
        'area' => 'extsession_main',
    ],
    'show_log' => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'extsession_main',
    ],
];