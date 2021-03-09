<?php
namespace wendor\core;
use \Dbmaster as ORM, \PDO;
/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 18.10.2017
 * Time: 02:25
 */
/**
 * Class Db
 * @package wendor\core
 * Реализует соединение с Базой Данных с использованием паттерна Singleton;
 * Производит запросы к Базам Данных согласно задач приложения
 * Используется вспомогательная библиотека RedBeenPHP версии актуальнной на день создания данного файла Db.php
 */
class Db
{
    /**
     * @var PDO - Содержит экземпляр объекта подключения к БД
     */
    protected $pdo;
    /**
     * @var object - Содержит единственный экземпляр объекта настоящего класса
     */
    protected static $instance;
    /**
     * @var int - Количество запросов
     */
    public static $countSql = 0;
    /**
     * @var array - Все запросы
     */
    public static $queries = [];

    /**
     * Db constructor.
     */
    protected function __construct()
    {
        $db = require ROOT . '/config/condbdefault.php';
        require LIBS . '/db_orm.php';
        ORM::setup($db['dns'], $db['user'], $db['pass']);
        ORM::freeze(true);
        ORM::fancyDebug(false);
        $this->pdo = new PDO($db['dns'], $db['user'], $db['pass'], [
            //PDO::ATTR_PERSISTENT => true,
            PDO::FETCH_ASSOC => PDO::FETCH_ASSOC
        ]);
    }

    /**
     *  В случае попытки клонировать объект настоящего класса возвращает текущий единственный экземпляр
     */
    protected function __clone()
    {
        if (self::$instance !== null) return self::$instance;
        return false;
    }

    /**
     * @return object|Db - Возвращает контроллеру объект настоящего класса с объектом подключения к БД
     */
    public static function instance()
    {
        if (self::$instance === null) self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param $sql  - Входящая строка запроса к БД [Update]
     * @return bool - Результат выполнения запроса к БД
     */
    public function execute($sql)
    {
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute();
    }

    /**
     * @param array $params - Параметры запроса на добавление новой записи в буферную таблицу
     * @return int
     */
    public function insert($params = []) : int
    {
        $cell = $params['cell'];
        $p_numb = $params['p_num'];
        $free = '0';
        $user_id = $params['user_id'];
        $table = $params['table'];
        $time = $params['timestamp'];

        $sql = "INSERT " .
                "INTO `$table` " .
                "SET `cells`='$cell', `p_numb`='$p_numb', `free`='$free', `user_id`='$user_id', `dates`='$time'";
        ORM::exec($sql);
        return ORM::getInsertID();
    }

    /**
     * @param $row
     */
    public function rowDelete($row)
    {
        ORM::trash($row);
    }

    /**
     * @param $table string        - Входящая строка запроса к БД [Select *]
     * @param $order string||false - Order By запроса к БД [Select *]
     * @return mixed               - Возвращает массив с всеми результами запроса к БД
     */
    public function queryAll($table, $order)
    {
        $res = ORM::findAll($table, $order);
        if ($res !== null) return $res;
        return [];
    }

    /**
     * @param $sql
     * @param $value
     * @return array
     */
    public function getAll($sql, $value) : array
    {
        $res = ORM::getAll($sql, $value);
        if ($res !== null) return $res;
        return [];
    }

    /**
     * @param $table  - Таблица поиска
     * @param $sql    - Входящая строка запроса к БД [Select mixed Where mixed]
     * @param $value  - Параметры запроса к БД
     * @return array|mixed
     */
    public function query($table, $sql, $value)
    {
        $res = ORM::find($table, $sql, $value);
        if ($res !== null) return $res;
        return [];
    }

    /**
     * @param $table - Таблица с которой работает запрос
     * @param $id    - id искомой строки
     * @return mixed - Возвращает первое совпадение запроса к БД
     */
    public function single($table, $id)
    {
        $res = ORM::load($table, $id);
        if ($res !== null) return $res;
        return [];
    }

    /**
     * @param $table
     * @param $sql
     * @param $value
     * @return array|\RedBeanPHP\OODBBean
     */
    public function findOne($table, $sql, $value)
    {
        $res = ORM::findOne($table, $sql, $value);
        if ($res !== null) return $res;
        return [];
    }
    /**
     * @param $table  - Таблица для запроса
     * @param $column - Колонка(поле) в которой происходит поиск
     * @param $value  - Значение, по которому будет осуществляться поиск в колонке
     * @return mixed  - Возвращает массив с всеми результами запроса к БД, либо null
     */
    public function column($table, $column, $value)
    {
        return ORM::findOne($table, $column, $value);
    }

    /**
     * @param string $sql_query
     * @param array $params
     * @return array
     */
    public function sqlQuery($sql_query = '', $params = [])
    {
        $sql = $this->pdo->prepare($sql_query);
        $sql->execute($params);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
}