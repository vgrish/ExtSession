<?php

/** @var Array $_lang */

$_lang = array_merge($_lang, [
    'area_extsession_main' => 'Основные',

    'setting_extsession_bot_patterns' => 'Шаблон ботов',
    'setting_extsession_bot_patterns_desc' => 'Регистронезависимый список User-Agent ботов, разделитель "|". По умолчанию - "Yandex|Google|Yahoo|Rambler|Mail|Bot|Spider|Snoopy|Crawler|Finder|curl|Wget|Go-http-client"',

    'setting_extsession_bot_gc_maxlifetime' => 'Время жизни сессии бота',
    'setting_extsession_bot_gc_maxlifetime_desc' => 'Время жизни сессии бота в секундах. Если не указан, то равно времени жизни по умолчанию - настройка "session_gc_maxlifetime"',

    'setting_extsession_empty_user_id_gc_maxlifetime' => 'Время жизни сессии Не-авторизованного пользователя',
    'setting_extsession_empty_user_id_gc_maxlifetime_desc' => 'Время жизни сессии для Не-авторизованного пользователя в секундах. Если не указан, то равно времени жизни по умолчанию - настройка "session_gc_maxlifetime"',

    'setting_extsession_not_empty_user_id_gc_maxlifetime' => 'Время жизни сессии для Авторизованного пользователя',
    'setting_extsession_not_empty_user_id_gc_maxlifetime_desc' => 'Время жизни сессии для Авторизованного пользователя в секундах. Если не указан, то равно времени жизни по умолчанию - настройка "session_gc_maxlifetime"',

    'setting_extsession_empty_user_agent_gc_maxlifetime' => 'Время жизни сессии с пустым User-Agent',
    'setting_extsession_empty_user_agent_gc_maxlifetime_desc' => 'Время жизни сессии с пустым User-Agent в секундах. Если не указан, то равно времени жизни по умолчанию - настройка "session_gc_maxlifetime"',

    'setting_extsession_show_log'=> 'Показать лог работы',
    'setting_extsession_show_log_desc'=> 'Показать лог работы. Выводит отладочную информацию в журнал ошибок',
]);

