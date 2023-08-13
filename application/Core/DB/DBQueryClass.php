<?php

namespace Aladser\Core\DB;

use PDO;
use PDOException;

/** Класс запросов в БД на основе PDO */
class DBQueryClass
{
    private $dbConnection;
    private string $host;
    private string $nameDB;
    private string $userDB;
    private string $passwordDB;

    public function __construct($host, $nameDB, $userDB, $passwordDB)
    {
        $this->host = $host;
        $this->nameDB = $nameDB;
        $this->userDB = $userDB;
        $this->passwordDB = $passwordDB;
    }

    private function connect()
    {
        try {
            $this->dbConnection = new PDO(
                "mysql:dbname=$this->nameDB; host=$this->host",
                $this->userDB,
                $this->passwordDB
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    private function disconnect()
    {
        $this->dbConnection = null;
    }

    /** выполняет подготовленный запрос
     * @param string $sql sql-запрос
     * @param array $args массив переменных запроса
     * @param bool $isOneValue одно или множество запрашиваемых полей
     * @return mixed массив строк или одно значение
     */
    public function queryPrepared(string $sql, array $args, bool $isOneValue = true)
    {
        $this->connect();
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($args);
        $this->disconnect();
        return $isOneValue ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll();
    }

    /** выполняет запрос
     * @param string $sql запрос
     * @param bool $isOneValue одно или множество запрашиваемых полей
     * @return mixed массив строк или одно значение
     */
    public function query(string $sql, bool $isOneValue = true)
    {
        $this->connect();
        $query = $this->dbConnection->query($sql);
        $this->disconnect();
        return $isOneValue ? $query->fetch(PDO::FETCH_ASSOC) : $query->fetchAll();
    }

    /** выполняет изменения данных
     * @param string $sql запрос
     * @return false|int число измененных строк
     */
    public function exec(string $sql)
    {
        $this->connect();
        $numRows = $this->dbConnection->exec($sql);
        $this->disconnect();
        return $numRows;
    }

    /** выполняет процедуру с возвращаемым результатом
     * @param mixed $sql выражение
     * @param mixed $out выходная переменная, куда будет возвращен результат
     */
    public function executeProcedure($sql, $out)
    {
        $this->connect();
        $stmt = $this->dbConnection->prepare("call $sql");
        $stmt->execute();
        $stmt->closeCursor();
        $procRst = $this->dbConnection->query("select $out as info");
        $this->disconnect();
        return $procRst->fetch(PDO::FETCH_ASSOC)['info'];
    }
}
