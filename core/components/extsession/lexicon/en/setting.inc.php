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

    'setting_extsession_limit_clearing' => 'Лимит очистки сессии',
    'setting_extsession_limit_clearing_desc' => 'Лимит очистки сессии. Подбирается опытным путем в зависимости от посещаемости сайта и мощности сервера',

    'setting_extsession_standart_clearing' => 'Стандартный запрос очистки сессии',
    'setting_extsession_standart_clearing_desc' => 'Активирует стандартный запрос очистки сессии. Полезно для отладки',

    'setting_extsession_show_log' => 'Показать лог работы',
    'setting_extsession_show_log_desc' => 'Показать лог работы. Выводит отладочную информацию в журнал ошибок',
]);

