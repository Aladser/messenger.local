<?php
namespace core\db;

use \PDO;
use \PDOException;

/** Класс запросов в БД на основе PDO */
class DBQueryCtl
{
    private $dbConnection;
    private $host;
    private $nameDB;
    private $userDB;
    private $passwordDB;

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
                $this->passwordDB,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    private function disconnect()
    {
        $this->dbConnection = null;
    }

    /**
     * выполняет запрос
     * @param string $sql запрос
     * @param bool $isOneValue число требуемых полей
     * @return mixed SQL-данные
     */
    public function query(string $sql, bool $isOneValue = true)
    {
        $this->connect();
        $query = $this->dbConnection->query($sql);
        $this->disconnect();
        return $isOneValue ? $query->fetch(PDO::FETCH_ASSOC) : $query->fetchAll();
    }

    /**
     * выполняет изменения данных
     * @param string $sql запрос
     * @return mixed число измененных строк
     */
    public function exec(string $sql)
    {
        $this->connect();
        $numRows = $this->dbConnection->exec($sql);
        $this->disconnect();
        return $numRows;
    }

    /**
     * выполняет процедуру с возвращаемым результатом
     * @param mixed $sql выражение
     * @param mixed $out выходная переменная, куда будет возвращен результат
     */
    public function executeProcedure($sql, $out)
    {
        $this->connect();
        $stmt = $this->dbConnection->prepare("call $sql");
        $stmt->execute();
        $stmt->closeCursor();
        $rslt = $this->dbConnection->query("select $out as info");
        $this->disconnect();
        return $rslt->fetch(PDO::FETCH_ASSOC)['info'];
    }
}
