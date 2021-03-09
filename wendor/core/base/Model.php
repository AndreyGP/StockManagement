<?php
namespace wendor\core\base;
use wendor\core\Db;
use wendor\core\base\SocketModel;

/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 22:30
 */

/**
 * Class Model
 * @package wendor\core\base
 * Реализует родительский класс для всех дочерних классов Модели
 */
abstract class Model
{
    /**
     * @var object|Db          - Объект класса Db с объектом подключения к БД
     */
    protected $pdo;

    /**
     * @var object|SocketModel - Объект класса SocketModel с объектом класса Socket
     */
    protected $socket;

    /**
     * @var - Текущая таблица БД
     */
    protected string $table;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->pdo    = Db::instance();
        $this->socket = new SocketModel();
    }

    /**
     * @param $table
     * @param $order
     * @return array - Массив с результатом полной выборки из таблицы БД
     */
    public function findAll($table = null, $order = false)
    {
        if ($table === null) $table = $this->table;
        return $this->pdo->queryAll($table, $order);
    }

    /**
     * @param $table
     * @param $id
     * @return array
     */
    public function load($table = null, $id)
    {
        if ($table === null) $table = $this->table;
        return $this->pdo->single($table, $id);
    }

    /**
     * @param $row
     */
    public function rowDelete($row)
    {
        $this->pdo->rowDelete($row);
    }

    /**
     * @param $table
     * @param $sql - Обёртка над методом query() класса Db
     * @param $value
     * @return array|mixed  - Полученный результат выборки из БД
     */
    public function find($table, $sql, $value)
    {
        return $this->pdo->query($table, $sql, $value);
    }

    /**
     * @param $sql
     * @param $value
     * @return array
     */
    public function getAll($sql, $value)
    {
        return$this->pdo->getAll($sql, $value);
    }

    /**
     * @param $table
     * @param $sql
     * @param $value
     * @return array|\RedBeanPHP\OODBBean
     */
    public function findOne($table, $sql, $value)
    {
        return $this->pdo->findOne($table, $sql, $value);
    }

    /**
     * @param $table - Обёртка над методом column() класса Db
     * @param $column
     * @param $value
     * @return array - Полученный результат выборки из БД
     */
    public function findColumn($table, $column, $value)
    {
        $column = " $column = ? ";
        return $this->pdo->column($table, $column, $value);
    }

    /**
     * @param $params - Обёртка над методом insert() класса Db
     * @return int    - ID новой вставки в таблицу БД
     */
    public function insert($params)
    {
        return $this->pdo->insert($params);
    }

    /**
     * @param $sql  - Обёртка над методом execute() класса Db
     * @return bool - Полученный результат обновления в таблице БД
     */
    public function update($sql)
    {
        return $this->pdo->execute($sql);
    }

    /**
     * @param string $sql_query
     * @param array $sql_params
     * @return array
     */
    public function sqlQuery($sql_query = '', $sql_params = [])
    {
        return $this->pdo->sqlQuery($sql_query, $sql_params);
    }

}