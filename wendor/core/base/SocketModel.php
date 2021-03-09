<?php
/**
 * Created by UnityWorld Framework.
 * @author: Andrei G. Pastushenko
 * Date: 18.01.2018
 * Time: 1:37
 * FileName: SocketModel.php
 */

namespace wendor\core\base;
use wendor\core\Socket;

/**
 * Class SocketModel
 * @package wendor\core\base
 */
class SocketModel
{
    /**
     * @var Socket
     */
    private $master;

    /**
     * SocketModel constructor.
     */
    public function __construct()
    {
        $this->master = Socket::instance();
    }

    /**
     * @param $pipe_name     - Название публичного канала
     * @param $event_in_pipe - Название события канала
     * @param $text_message  - Данные для передачи в публичный канал по событию
     * @description          - Формирует конечный запрос на отправку данных в публичный канал по событию
     */
    public function sendPublicMessage($pipe_name, $event_in_pipe, $text_message)
    {
        $this->master->sendPublicMessage("INSERT INTO pipes_messages (name, event, message)VALUES(\"$pipe_name\", \"$event_in_pipe\", \"$text_message\");");
    }

    /**
     * @param $id            - ID Пользователя, которому гарантировано будут доставлены данные
     * @param $event         - Событие данных
     * @param $text_message  - Данные для гарантированной доставки
     * @description          - Формирует конечный запрос на гарантированную доставку данных конкретному пользователю
     */
    public function sendMessageFromUserID($id, $event, $text_message)
    {
        $query = "INSERT INTO users_messages (id, event, message)VALUES ($id, $event, $text_message)";
        $this->master->sendPrivateMessage($query);
    }

    /**
     * @param $id            - ID авторизовавшегося пользователя
     * @param $hash          - Сессионный хэш пользователя
     * @description          - Формирует конечный запрос авторизации пользователя на сокет-сервере
     */
    public function userAuthSession($id, $hash)
    {
        $query = "INSERT INTO users_auth (id, hash )VALUES ($id, $hash)";
        $this->master->userAuthSession($query);
    }
}