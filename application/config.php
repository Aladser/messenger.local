<?php

namespace Aladser;

function config($param)
{
    // --- список глобальных параметров ---
    $paramList = [
        // подключение к БД
        'HOST_DB' => 'localhost',
        'NAME_DB' => 'messenger',
        'USER_DB' => 'admin',
        'PASS_DB' => '@admin@',

        // настройки почтового сервера
        'SMTP_SRV' => 'smtp.mail.ru',
        'EMAIL_USERNAME' => 'aladser@mail.ru',
        'EMAIL_PASSWORD' => 'BEt7tei0Nc2YhK4s1jix',
        'SMTP_SECURE' => 'ssl',
        'SMTP_PORT' => 465,
        'EMAIL_SENDER' => 'aladser@mail.ru',
        'EMAIL_SENDER_NAME' => 'Messenger Admin',

        // демон вебсокета сообщений
        'CHAT_WS_PORT' => 8888,
        'SITE_ADDR' => '127.0.0.1',
        'WEBSOCKET_PROCESS_NAME' => 'chat-server',

        'WEBSOCKET_PROCESS_FILE' => __DIR__.'/chat-server.php',
        'WEBSOCKET_PROCESS_LOGFILE' => dirname(__DIR__, 1).'/logs/websocket.log',
        'PIDLIST_FILE' => dirname(__DIR__, 1).'/logs/pids.log',
    ];

    try {
        if (array_key_exists($param, $paramList)) {
            return $paramList[$param];
        } else {
            throw new \Exception("Параметр $param не существует");
        }
    } catch (\Exception $ex) {
        exit($ex);
    }
}
