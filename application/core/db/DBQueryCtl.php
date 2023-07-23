<?php
namespace core\db;

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
        try{
           $this->dbConnection = new \PDO("mysql:dbname=$this->nameDB; host=$this->host", $this->userDB, $this->passwordDB, 
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        }
        catch(\PDOException $e){
            die($e->getMessage());
        }
    }

    private function disconnect(){
        $this->dbConnection = null;
    }
    
    //  подготавливает и выполняет оператор SQL без заполнителей
    public function query($sql, $isOneValue=true)
    {
        $this->connect();
        $query = $this->dbConnection->query($sql);
        $this->disconnect();
        return $isOneValue ? $query->fetch(\PDO::FETCH_ASSOC) : $query->fetchAll(); 
    }

    // выполняет оператор SQL в одном вызове функции, возвращая количество строк, затронутых оператором
    public function exec($sql)
    {
        $this->connect();
        $rslt = $this->dbConnection->exec($sql);
        $this->disconnect();
        return $rslt;
    }

    // выполняет процедуру с возвращаемым результатом
    /**
     * @param mixed $sql выражение
     * @param mixed $out выходная переменная, куда будет возвращен результат
     */
    public function executeProcedure($sql, $out)
    {
        $this->connect();
        $stmt = $this->dbConnection->prepare("call $sql");
        $stmt->execute();
        $stmt->closeCursor();
        $rslt =$this->dbConnection->query("select $out as info");
        $this->disconnect();
        return $rslt->fetch(\PDO::FETCH_ASSOC)['info'];
    }
}
?>
