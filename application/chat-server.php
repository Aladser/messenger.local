<?php

namespace Aladser;

use Aladser\Core\ConfigClass;
use Aladser\Core\Chat;
use Aladser\Core\DB\DBCtl;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

$dbCtl = new DBCtl(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB);
$users = $dbCtl->getUsers();
$connections = $dbCtl->getConnections();
$messages = $dbCtl->getMessageDBTable();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat($connections, $messages, $users)
        )
    ),
    ConfigClass::CHAT_WS_PORT
);
$server->run();
