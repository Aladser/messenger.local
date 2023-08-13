<?php

namespace Aladser\Core;

class ConfigClass
{
    // подключение к БД
    public const HOST_DB = 'localhost';
    public const NAME_DB = 'messenger';
    public const USER_DB = 'admin';
    public const PASS_DB = '@admin@';

    // настройки почтового сервера
    public const SMTP_SRV = 'smtp.mail.ru';
    public const EMAIL_USERNAME = 'aladser@mail.ru';
    public const EMAIL_PASSWORD = 'BEt7tei0Nc2YhK4s1jix';
    public const SMTP_SECURE = 'ssl';
    public const SMTP_PORT = 465;
    public const EMAIL_SENDER = 'aladser@mail.ru';
    public const EMAIL_SENDER_NAME = 'Messenger Admin';

    // демон вебсокета сообщений
    public const CHAT_WS_PORT = 8888;
    public const SITE_ADDR = '127.0.0.1';
    public const WEBSOCKET_PROCESSNAME = 'chat-server';

    public static function getWebSocketProcessFile(): string
    {
        return dirname(__DIR__, 1) . '/chat-server.php';
    }
    
    public static function getWebsocketProcessLogFile(): string
    {
        return dirname(__DIR__, 2) . '/logs/websocket.log';
    }

    public static function getPidsListFile(): string
    {
        return dirname(__DIR__, 2) . '/logs/pids.log';
    }
}
