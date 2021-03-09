<?php
/**
 * Created by UnityWorld Framework.
 * @author: Andrei G. Pastushenko
 * @copyright : Unity World Online
 * @date: 30.12.2017
 * @time: 11:46
 */

namespace app\controllers;

/**
 * Класс создания сокет-серверов и работы с соединениями посредством использования socket_*
 * Класс позволяет создавать разные сокет-серверы по входящим параметрам
 * Класс создан максимально гибким и универсальным
 */
class SocketController
{
    /**
     * Константы для рукопожатия
     */ 
     
    const HEADER_HTTP1_1                        = 'HTTP/1.1 101 Web Socket Protocol Handshake',
          HEADER_WEBSOCKET_ACCEPT_HASH          = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    const HEADERS_UPGRADE_KEY                   = 'Upgrade',
          HEADERS_CONNECTION_KEY                = 'Connection',
          HEADERS_SEC_WEBSOCKET_ACCEPT_KEY      = 'Sec-WebSocket-Accept';
    const HEADERS_UPGRADE_VALUE                 = 'websocket',
          HEADERS_CONNECTION_VALUE              = 'Upgrade';
    const HEADERS_EOL                           = "\r\n";
    const SEC_WEBSOCKET_KEY_PTRN                = '/Sec-WebSocket-Key:\s(.*)\n/';
    const HEADERS_SEC_WEB_SOCKET_PROTOCOL_KEY   = 'Sec-WebSocket-Protocol',
          HEADERS_SEC_WEB_SOCKET_PROTOCOL_VALUE = 'chat';
     
    /**
     * 
     */
     public $server_listening = true;
    
    /**
     * Экземпляр сокет-ресурса
     */
    private $socket_server;

    /**
     * Массив с сокет-клиентами
     */
    private $socket_clients = [];

    /**
     * Уровни действующей ошибки и её текстовое сообщение
     */
    public $errno;
    public $errorMessage;

    /**
     * Параметры сокет-сервера
     */
    private static $ip;
    private static $port;

    /**
     * Конструктор класса
     * Принимает на вход:
     * @param string $ip       - адрес создаваемого демона сокет-сервера
     * @param string|int $port - порт шлюза, который будет прослушиваться демоном сокета
     * @Description: Принимаемые параметры записываются в защищённые статические свойства (::$ip, ::$port) класса и уничтожаются,
     *  далее заданные значения свойств класса используются в вызываемом конструктором методе ::setSocketServer()
     */
    public function __construct($ip, $port)
    {
        self::$ip            = $ip;
        self::$port          = $port;

        self::setSocketServer();
    }

    /**
     * Запрещает клонирование класса/объекта
     */
    private function __clone()
    {
    }

    /**
     * Деструктор класса
     * Перед уничтожением объекта класса приводит все защищённые свойства класса в (bool)false, открытые уничтожает
     */
    public function __destruct()
    {
        $this->socket_server  = false;
        $this->socket_clients = false;
        self::$ip             = false;
        self::$port           = false;

        unset($this->errno, $this->errorMessage);
    }

    /**
     * Защищённый метод создания сокета TCP
     */
    private function setSocketServer()
    {
        $this->socket_server  = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        self::setSocketOptions();
    }
    
    /**
     * Защищённый метод, разрешающий использование одного порта для нескольких соединений
     */
    private function setSocketOptions()
    {
        socket_set_option($this->socket_server, SOL_SOCKET, SO_REUSEADDR, 1);
        self::bindSocketServer();
    }
    
    /**
     * Защищённый метод привязки имени сокета к определённому адрессу и порту 
     */
    private function bindSocketServer()
    {
        socket_bind($this->socket_server, self::$ip, self::$port);
        
        $this->errno        = socket_last_error($this->socket_server);
        $this->errorMessage = socket_strerror($this->errno);
    }
    

    /**
     * Метод, реализующий передачу созданного потока демону-заказчику сокет-сервера
     * Принимает:
     * @param mixed $my_log - данный параметр содержит дескриптор подключения к открытому для записи файлу логов
     * В данном методе осуществляется "отложенная" проверка на ошибку создания потока. Если ошибка произошла, то
     * производится запись в файл лога ошибок, с его закрытием. После выбрасывается исключение throw.
     * При положительном результате метод возвращает созданный поток демону-заказчику
     * @return mixed|bool
     */
    public function getSocketServer($my_log)
    {
        if ($this->socket_server === false){

            fwrite($my_log, date("d.m.Y H:i:s") . ": Сокет не создан! Причина " . $this->errorMessage . "\n\r");
            fclose($my_log);

            throw new \UnexpectedValueException('Сокет не создан по причине: '
                                                              . $this->errorMessage . "\n\r",
                                                                $this->errno);
        }
        return $this->socket_server;
    }

    /**
     * Метод добавления клиента нового сокет подключения в массив со всеми текущими подключениями
     * Принимает:
     * @param mixed $client      - название удалённого сокетного соединения
     * Добавляет элемент массива с названием удалённого сокетного соединения, присваивая в качестве ключа значение
     * текущего сессионного идентификатора (рнр куки)
     * После удаляет переданные параметры
     */
    public function setClients($client)
    {
        $this->socket_clients[] = $client;
    }

    /**
     * Метод передачи массива с названиями текущих сокетных подключений по запросу демона заказчика
     * @return array - Возвращает массив с именами сокет подключений, хранящихся в защищённом свойстве класса
     */
    public function getClients()
    {
        return $this->socket_clients;
    }

    /**
     * Метод удаления элемента массива с названием одного из сокетных подключений
     * Принимает:
     * @param mixed $client - сессионный индефикатор, полученный из сессионой куки РНР
     * @Description: Данный метод возможен к использованию только в случае корректного завершения соединения при выходе из приложения
     * Для удаления имен разорванных соединений по причине закрытия браузера, вкладки или потери интернет соединения,
     * используются другие методы, исходя из результатов анализа имеющихся подключений демоном сокет-сервера
     */
    public function unsetClient($client)
    {
        unset($this->socket_clients[array_search($client, $this->socket_clients)]);
    }

    /**
     * Метод обратного рукопожатия
     * Принимает:
     * @param resource $connect - ресурс входящего подключения
     * @param resource $my_log  - дескриптор файла лога
     * @Description: На основе ключа Sec-WebSocket-Key, переданным браузером в заголовке, формируется загаловок ответа,
     * с необходимым Sec-WebSocket-Accept.
     * @return array | bool  $info - возвращает готовый заголовок ответа
     */
    public function handshake($connect, $my_log) {
        $info               = array();

        $header             = socket_read($connect, 1024);
        //fwrite($my_log, "========================================================\r\n\r\n");
        //fwrite($my_log, "Получен заголовок клиента: " . $header . "\r\n\r\n");
        //fwrite($my_log, "========================================================\r\n\r\n");
        $header             = explode("\r\n", $header);
        //fwrite($my_log, "Получен массив: " . print_r($header, true) . "\r\n\r\n");
        //fwrite($my_log, "========================================================\r\n\r\n");
        //fwrite($my_log, "Побежали по циклу:\r\n");
        //считываем заголовки из соединения
        foreach ($header as $line) {

            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {

                $info[$matches[1]]
                            = $matches[2];
                
          //  fwrite($my_log, "Имя строки: " . $matches[1] . " || Значение: " . $matches[2] . "\r\n");
        
            }
        }
        
        //fwrite($my_log, "Конец цикла\r\n\r\n");
        //fwrite($my_log, "========================================================\r\n\r\n");
        socket_getpeername($connect, $client_ip, $client_port);
        $info['ip']         = $client_ip;
        $info['port']       = $client_port;
        //fwrite($my_log, "IP клиента {$client_ip} на порту {$client_port}\r\n\r\n");
        //fwrite($my_log, "========================================================\r\n\r\n");
        
        if (!isset($info['Sec-WebSocket-Key']) || empty($info['Sec-WebSocket-Key'])) {
            socket_close($connect);
            unset($header, $line, $info);
            return false;
        }

        //отправляем заголовок согласно протоколу вебсокета
        $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . self::HEADER_WEBSOCKET_ACCEPT_HASH)));

        $upgrade            = self::HEADER_HTTP1_1                                                 . self::HEADERS_EOL
            . self::HEADERS_UPGRADE_KEY .    ": " . self::HEADERS_UPGRADE_VALUE    . self::HEADERS_EOL
            . self::HEADERS_CONNECTION_KEY . ": " . self::HEADERS_CONNECTION_VALUE . self::HEADERS_EOL
            . self::HEADERS_SEC_WEBSOCKET_ACCEPT_KEY . ": " . $SecWebSocketAccept  . self::HEADERS_EOL           
            . self::HEADERS_EOL;
        socket_write($connect, $upgrade, strlen($upgrade));
        //fwrite($my_log, "Отправляем заголовок ответа:\r\n");
        //fwrite($my_log, $upgrade . "\r\n\r\n");
        //fwrite($my_log, "=====================FIN================================\r\n");
        unset($upgrade, $SecWebSocketAccept, $address, $header, $line);

        return $info;
    }

}