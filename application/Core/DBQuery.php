<?php

namespace App\Core;

use RedBeanPHP\R;

/** Класс запросов в БД на основе PDO */
class DBQuery
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

        try {
            $this->dbConnection = new \PDO(
                "mysql:dbname=$this->nameDB; host=$this->host",
                $this->userDB,
                $this->passwordDB
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        /*
        R::setup('mysql:host=localhost;dbname=redbeanphp', 'root', '', false);
        if (!R::testConnection()) {
            exit('No DB connection!');
        }
        */
    }

    /** выполняет подготовленный запрос
     * @param string $sql        sql-запрос
     * @param array  $args       массив переменных запроса
     * @param bool   $isOneValue одно или множество запрашиваемых полей
     *
     * @return mixed массив строк или одно значение
     */
    public function queryPrepared(string $sql, array $args, bool $isOneValue = true)
    {
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($args);

        return $isOneValue ? $stmt->fetch(\PDO::FETCH_ASSOC) : $stmt->fetchAll();
    }

    /** выполняет запрос
     * @param string $sql        запрос
     * @param bool   $isOneValue одно или множество запрашиваемых полей
     *
     * @return mixed массив строк или одно значение
     */
    public function query(string $sql, bool $isOneValue = true)
    {
        $query = $this->dbConnection->query($sql);

        return $isOneValue ? $query->fetch(\PDO::FETCH_ASSOC) : $query->fetchAll();
    }

    /** выполняет изменения данных.
     * @param string $sql запрос
     *
     * @return false|int число измененных строк
     */
    public function exec(string $sql)
    {
        $numRows = $this->dbConnection->exec($sql);

        return $numRows;
    }

    /** выполняет процедуру с возвращаемым результатом
     * @param mixed $sql выражение
     * @param mixed $out выходная переменная, куда будет возвращен результат
     */
    public function executeProcedure($sql, $out)
    {
        $stmt = $this->dbConnection->prepare("call $sql");
        $stmt->execute();
        $stmt->closeCursor();
        $procRst = $this->dbConnection->query("select $out as info");

        return $procRst->fetch(\PDO::FETCH_ASSOC)['info'];
    }

    /** INSERT.
     *
     * @param string $tableName  имя таблицы
     * @param array  $fieldArray внешние данные
     */
    public function insert(string $tableName, array $fieldArray): int
    {
        // поля
        $fieldNames = implode(', ', array_keys($fieldArray));
        // значения полей
        $fieldValues = '';
        foreach (array_keys($fieldArray) as $value) {
            $fieldValues .= ':'.$value.', ';
        }
        $fieldValues = mb_substr($fieldValues, 0, strlen($fieldValues) - 2);
        // запрос
        $sql = "insert into $tableName($fieldNames) values($fieldValues)";

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($fieldArray);
        $id = $this->dbConnection->lastInsertId();

        return $id;
    }

    /** DELETE.
     *
     * @param string $tableName      имя таблицы
     * @param string $whereCondition условие WHERE
     * @param array  $args           внешние данные
     */
    public function delete(string $tableName, string $whereCondition, array $args): bool
    {
        $sql = "delete from $tableName where $whereCondition";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($args);
        $rowCount = $stmt->rowCount();

        return $rowCount > 0;
    }

    /** UPDATE.
     *
     * @param string $tableName  имя таблицы
     * @param array  $fieldArray внешние данные
     * @param array  $condition  массив условия [поле, знак условия, значение поля]
     */
    public function update(string $tableName, array $fieldArray, array $condition = null): bool
    {
        // sql
        $sql = "update $tableName set ";
        $condition_field_name = $condition['condition_field_name'];
        $condition_sign = $condition['condition_sign'];
        $condition_field_value = $condition['condition_field_value'];

        foreach (array_keys($fieldArray) as $fieldName) {
            $sql .= "$fieldName = :$fieldName, ";
        }
        $sql = mb_substr($sql, 0, strlen($sql) - 2);

        if (!empty($condition)) {
            $sql .= " where $condition_field_name $condition_sign :$condition_field_name";
        }

        // args
        $args = $fieldArray;
        $args[$condition_field_name] = $condition_field_value;

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($args);
        $rowCount = $stmt->rowCount();

        return $rowCount > 0;
    }

    public function updateDiffCondition(
        string $tableName,
        array $fieldArray,
        array $condition = null,
        array $conditionValuesArray = null
    ): bool {
        // sql
        $sql = "update $tableName set ";

        foreach (array_keys($fieldArray) as $fieldName) {
            $sql .= "$fieldName = :$fieldName, ";
        }
        $sql = mb_substr($sql, 0, strlen($sql) - 2);

        if (!empty($condition)) {
            $sql .= " where $condition";
            $args = array_merge($fieldArray, $conditionValuesArray);
        } else {
            $args = $fieldArray;
        }

        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($args);
        $rowCount = $stmt->rowCount();

        return $rowCount > 0;
    }
}
