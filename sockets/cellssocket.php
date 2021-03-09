<?php
cli_set_process_title('cells_socket_daemon_' . getmypid());
/*
 * DAEMON CELLS_SOCKET
 * Принимает и передаёт всем находящихся пользователям изменения на странице ~/buffer (подтверждение отражения в 1С),
 * а так же передаёт с индексной страницы на ~/buffer, если на ~/buffer кто-то работает, вновь добавленные позиции.
 */
//define('PATH_TO_CONTROLLERS','/var/www/stockirb/data/www/stockirbis.ru/app/controllers/');

include_once '../app/controllers/SocketController.php';
use app\controllers\SocketController;

//Настраивает вывод ошибок
error_reporting(E_ALL);

/* Позволяет скрипту ожидать соединения бесконечно. */
set_time_limit(0);

/* Включает скрытое очищение вывода так, что мы получаем данные
 * как только они появляются. */
ob_implicit_flush();

//Текущая директория
$baseDir = dirname(__FILE__);

//Переназначает директорию расположения лога ошибок для данного скрипта
ini_set('error_log',$baseDir.'/logs/error.log');
//Выделяет больше памяти скрипту
ini_set('memory_limit', '128M');

//Закрывает все выводы, чтобы не осуществлялся вывод в консоль
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);

//Переназначает закрытые консольные выводы в файлы и назначает свой пользовательский лог
$STDIN   = @fopen($baseDir.'/dev/null', 'r');
$STDOUT  = fopen($baseDir.'/logs/application.log', 'a');
$STDERR  = fopen($baseDir.'/logs/daemon.log', 'a');
$my_log  = fopen($baseDir.'/logs/my_log.log', 'a');

//Записываем в лог номер процесса
$pid_log = fopen($baseDir.'/logs/my_pid.log', 'w');
$pid     = getmypid();
fwrite($pid_log, date("d.m.Y H:i:s") . ": Текущий № PID процесса[cellssocket.php] демона: ". $pid . "\n\r");
fclose($pid_log);
unset($pid);

//Параметры демона сокет-сервера
$address = '127.0.0.1';
$port    = 5454;//3315-3331, 4130-4221, 5115-5120, 5450-5490, 6020-6045, 6525-6540, 6700-6767, 7050-7070, 7150-7170,
                 //8020-8070, 8121-8171, 8228-8282, 8448-8484, 8505-8558, 27072-27372, !!!48654—48999!!!

//Объект класса, принимающий в конструктор параметры демона сокет-сервера
$master  = new SocketController($address, $port);

//Получение заказанного потока сокет-сервера
$server  = $master->getSocketServer($my_log);

//Блок с валидациями на корректность и полноту создания сокет-сервера
if (!$server || !socket_listen($server)){

    fwrite($my_log, date("d.m.Y H:i:s") . ": Ошибка сокета или его прослушивания: "
                                                      . "{$master->errorMessage}({$master->errno})" . "\r\n");
    fclose($my_log);
    fclose($STDIN);
    fclose($STDOUT);
    fclose($STDERR);
    exit();
}

//Задаём не блокирующий режим
//socket_set_nonblock($server);

//Помещаем созданный сокет-сервер в массив всех сокетов для дальнейшего прослушивания
$master->setClients($server);

/* Счётчик итераций цикла, который обнуляется каждые 1000КК итераций, после принудительного запуска Garbage Collector'а
 * через вызов gc_collect_cycles()[Явным образом запускает механизм поиска циклических ссылок] для очистки
 * и предупреждения утечек памяти. Число итераций актуально для неблокирующего режима, в противном случае их количество
 * необходимо значительно уменьшить
 */
$iterator = 0;

/*
 * Демонизация сокет-сервера посредством условно бесконечного цикла
 */
while ($master->server_listening){
    
    $read           = $write
                    = $master->getClients();
    $except         = null;
    $write          = null;
                  
    if ( ($num_changed_socket = socket_select($read, $write, $except, 0)) === false ) break;
    if (  $num_changed_socket < 1) continue;
    
    unset($read[array_search($server, $read)]);
    
    $count_client   = count($read);
    
    if ($count_client < 1){
        if ( ($client   = socket_accept($server)) !== false ){
        
            fwrite($my_log, "Подключен клиент: " . $client . "\r\n");
        
            if (!array_search($client, $read)){
                
                if ( ($info = $master->handshake($client, $my_log)) === false){
                fwrite($my_log, date("d.m.Y H:i:s") . " Полная херня с рукопожатием!\r\n");
                continue;
                }
                
                fwrite($my_log, "Это новый клиент: " . $client . "\r\n");
                fwrite($my_log, "Данные клиента: \r\n" . print_r($info, true) . "\r\n");
                
                $master->setClients($client);
                continue;
            }
        }
    }
    
    /*if ($count_client == 1){
        //sleep(5);
        continue;
    }*/
    
    fwrite($my_log, "Сейчас подключены клиенты(" . $count_client . "шт): \r\n" . print_r($read, true) . "\r\n");
    
    foreach ($read as $read_sock) {   
        fwrite($my_log, "Читаем клиент: " . $read_sock . "\r\n");
        $data       = socket_read($read_sock, 256, PHP_NORMAL_READ );
        
        if ($data === false) {
            
            unset($read[array_search($read_sock, $read)]);
            $master->unsetClient($read_sock);
            continue;
        }
        
        $data = trim($data);
        fwrite($my_log, "Пришло от клиента: " . $data . "\r\n");
        if (!empty($data)) {
            
            foreach ($read as $send_sock) {
            
                if ($send_sock == $server || $send_sock == $read_sock) continue;
                fwrite($my_log, "Отправляем клиенту: " . $send_sock . "\r\n");
                socket_write($send_sock, $data."\r\n");
                
            } // endforeach
            unset($data);            
        }
        
    } // end of reading foreach
    
    //На каждой 100КК итерации...
    if ($iterator == 1000000000){
        //...проверяем состояние готовности сборщика мусора и...
        if (gc_enabled()){
            //...если всё включено, то запускаем очистку мусора...
            gc_collect_cycles();
            fwrite($my_log, date("d.m.Y H:i:s") . ": Произведена очистка памяти на 1 000 000 000 итерации\n\r");
        } else { //...иначе включаем сборщик и запускаем его
            gc_enable();
            gc_collect_cycles();
        }
        // После очистки памяти обнуляем счётчик итераций
        $iterator = 0;

    } else {

        $iterator++; //Считаем итерации
    }
//fwrite($my_log, "Конец итерации {$iterator}\r\n");
//fwrite($my_log, "============================================================================\r\n");
    unset($client, $read, $write, $except, $data); //Убиваем всё использованное временное*/
}

fclose($server);
$master = NULL;
unset($server, $master, $iterator);