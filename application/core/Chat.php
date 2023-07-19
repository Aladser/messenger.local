<?php
 
namespace core;
use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
date_default_timezone_set('Europe/Moscow');

// Чат
class Chat implements MessageComponentInterface {
    private $clients;           // хранение всех подключенных пользователей
    private $connectionsTable;  // таблица подключений
    private $messageTable;      // таблица сообщений
    private $usersTable;      // таблица сообщений
    private $logFile;
    private $logfileContent;
   
    public function __construct(\core\db\ConnectionsDBTableModel $connectionsTable, \core\db\MessageDBTableModel $messageTable, \core\db\UsersDBTableModel $usersTable){
        $this->clients = new \SplObjectStorage;
        $this->connectionsTable = $connectionsTable;
        $this->messageTable = $messageTable;
        $this->usersTable = $usersTable;

        $this->connectionsTable->removeConnections(); // удаление старых соединений
        $this->logFile = dirname(__DIR__, 1).'/logs.txt';
        file_put_contents($this->logFile, "");
    }

    /** открыть соединение
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление клиента
        $message = json_encode([ 'onсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message); // рассылка остальным клиентам
    }
    
    /** закрыть соединение
     * @param ConnectionInterface $conn соединение
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $publicUsername = $this->connectionsTable->getConnectionPublicUsername( $conn->resourceId ); // публичное имя клиента
        $this->connectionsTable->removeConnection( $conn->resourceId ); // удаление соединения из БД
        $this->writeLog("Connection $publicUsername completed");
        $message = json_encode([ 'offсonnection' => 1, 'user' => $publicUsername]);
        foreach ($this->clients as $client) $client->send($message); 
    }

    /** получить соообщения от клиентов
     * @param ConnectionInterface $from соединение
     * @param mixed $msg сообщение
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // после соединения пользователь отправляет пакет messageOnconnection
        $data = json_decode($msg);
        if($data->messageOnconnection){
            $rslt = $this->connectionsTable->addConnection( ['author'=>$data->author, 'userId'=>$data->userId] ); // добавление соединения в БД
            $data->author = $rslt['publicUsername'] ? $rslt['publicUsername'] : ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo]; // имя пользователя или ошибка добавления
        }
        // новое сообщение пользователя
        else if($data->message){
            if($data->messageType == 'NEW'){
                $data->time = date('Y-m-d H:i:s');
                $data->chat_message_id = $this->messageTable->addMessage($data);
            }
            else if($data->messageType == 'EDIT'){
                $data = $this->messageTable->editMessage($data->message, $data->msgId);
            }
            else if($data->messageType == 'REMOVE'){
                $data = $this->messageTable->removeMessage($data->msgId);
            }
            else if($data->messageType == 'FORWARD'){
                $data->time = date('Y-m-d H:i:s');

                // добавляем Переслано один раз для переотправленных сообщений
                if(mb_strripos($data->message, "<div class=\"msg__forwarding\">Переслано</div>") === false){
                    $data->message = "<div class=\"msg__forwarding\">Переслано</div>" . $data->message;
                }

                // определяем чат, куда добавить
                $fromUserId = $this->usersTable->getUserId($data->fromuser);
                $toUserId = $this->usersTable->getUserId($data->touser);
                $data->chatId = $this->messageTable->getDialogId($fromUserId, $toUserId)."\n";


                $data->chat_message_id = $this->messageTable->addMessage($data);
            }
        }

        $msg = json_encode($data);
        echo $msg."\n";
        $this->writeLog($msg);
        foreach ($this->clients as $client) $client->send($msg);
    }

    /** ошибка подключения
     * @param ConnectionInterface $conn соединение
     * @param \Exception $e ошибка
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->writeLog("error: {$e->getMessage()}");
        $conn->close();
    }

    /** Запись логов
     * @param mixed $message лог
     */
    public function writeLog($message){
        file_put_contents($this->logFile, $message."\n", FILE_APPEND | LOCK_EX);
    }
}