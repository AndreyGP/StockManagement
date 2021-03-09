<?php
/**
 * Created by PhpStorm.
 * User: Andrei G. Pastushenko
 * Date: 17.01.2018
 * Time: 23:31
 */

namespace wendor\core;

/**
 * Class Socket
 * @package wendor\core\base
 * @description - Класс реализует серверный РНР интерфейс работы с сокет-сервером "CometQL"
 */
class Socket
{
    /**
     * @var \mysqli
     */
    private $master;

    protected static $instance;
    /**
     * Socket constructor.
     */
    protected function __construct()
    {
        $this->setMaster();
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
     * Создаёт дескриптор подключения к сокет-серверу
     */
    private function setMaster()
    {
        $dev_info = require ROOT . '/config/conssdefault.php';
        $this->master = mysqli_connect($dev_info['host'], $dev_info['dev_id'], $dev_info['dev_key'], $dev_info['db']);
    }

    /**
     * @return object|Db - Возвращает контроллеру объект настоящего класса с дескриптором подключения к сокет-серверу
     */
    public static function instance()
    {
        if (self::$instance === null) self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param $query - Запрос на отправку данных в публичный канал по событию
     * @description  - Данный метод доставки данных не подразумевает хранение данных до появления кого-то онлайн
     */
    public function sendPublicMessage($query)
    {
        if (!$this->master) $this->setMaster();
        mysqli_query($this->master, $query);
    }

    /**
     * @param $query - Запрос на отправку данных конкретному пользователю по событию
     * @description  - Данный метод гарантирует доставку переданых данных пользователю с ожиданием его выхода в онлайн
     */
    public function sendPrivateMessage($query)
    {
        if (!$this->master) $this->setMaster();
        mysqli_query($this->master, $query);
    }

    /**
     * @param $query - Запрос на авторизацию пользователя в системе
     * @description  - Данный метод авторизирует пользователя во время его входа в систему по его ID
     */
    public function userAuthSession($query)
    {
        if (!$this->master) $this->setMaster();
        mysqli_query($this->master, $query);
    }
}