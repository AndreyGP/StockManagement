<?php
namespace wendor\libs;
/**
 * Created by PhpStorm.
 * User:  Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 15:15
 */

/**
 * @param $array - принимаемая для дебага переменная или объект
 * Стандартная функция дебага
 */
function debug($array)
{
    echo "<pre>\n\r" . print_r($array, true) . "</pre>\n\r";
}

function debug_object($array)
{
    dmp($array);
}

/**
 * @param $controller - имя контроллера
 * @param $action - имя метода контроллера
 * @param $route - имя папки с видами контроллера
 * @return mixed $obj - возвращает объект класса контроллера
 * В режиме developer mode проверяет наличие необходимых классов контроллера и/или модели, метода класса контроллера,
 * а так же необходимые папку для файлов видов и сам файл вида метода контроллера в ней. Помогает разработчику следовать
 * внутренним соглашениям именований и соблюдать необходимую структуру приложения.
 */
function dev_mode ($controller, $action, $route)
{
    $controller_class = "\\app\\controllers\\" . $controller;
    if ( !class_exists($controller_class) ) {
        ob_start();
        include_once DEV_MODE . "/controller.tmp";
        $content = ob_get_clean();
        $title   = "Режим разработки";
        $file_layouts = VIEW . "/Layouts/default.tmp";
        (is_file($file_layouts))
            ? require_once $file_layouts
            : not_found();
        exit;
    }
    $model = "\\app\\models\\" . $route['controller'] . 'Model';
    if ( !class_exists($model) ) {
        ob_start();
        include_once DEV_MODE . "/model.tmp";
        $content = ob_get_clean();
        $title   = "Режим разработки";
        $file_layouts = VIEW . "/Layouts/default.tmp";
        (is_file($file_layouts))
            ? require_once $file_layouts
            : not_found();
        exit;
    }
    if ( !file_exists('../app/views/' .  $route['controller']  ) ){
        ob_start();
        include_once DEV_MODE . "/dir.tmp";
        $content = ob_get_clean();
        $title   = "Режим разработки";
        $file_layouts = VIEW . "/Layouts/default.tmp";
        (is_file($file_layouts))
            ? require_once $file_layouts
            : not_found();
        exit;
    }
    if ( !method_exists($obj = new $controller_class($route), $action)) {
        ob_start();
        include_once DEV_MODE . "/action.tmp";
        $content = ob_get_clean();
        $title   = "Режим разработки";
        $file_layouts = VIEW . "/Layouts/default.tmp";
        (is_file($file_layouts))
            ? require_once $file_layouts
            : not_found();
        exit;
    }
    if ( !file_exists('../app/views/' .  $route['controller'] . '/' . $route['action'] .= '.tmp') ) {
        ob_start();
        include_once DEV_MODE . "/view.tmp";
        $content = ob_get_clean();
        $title   = "Режим разработки";
        $file_layouts = VIEW . "/Layouts/default.tmp";
        (is_file($file_layouts))
            ? require_once $file_layouts
            : not_found();
        exit;
    }
    return $obj;
}

function object_controller_factory ($controller, $param = [])
{
    $controller_class = "\\app\\controllers\\" . $controller;
    if(!class_exists($controller_class)) not_found();
    $obj = new $controller_class($param);
    return $obj;
}

/**
 * Стандартная функция отработки 404 Not Found
 */
function not_found ()
{
    http_response_code(404);
    include '404.html';
    exit;
}

/**
 * @param $name - исходное значение имени
 * @return mixed - редактированное значение имени
 */
function upper_camel_case ($name)
{
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
}

/**
 * @param $name - исходное значение имени
 * @return mixed - редактированное значение имени
 */
function lower_camel_case ($name)
{
    return lcfirst(upper_camel_case($name));
}

/**
 * @param $query - входящая строка запроса
 * @return string - отредактированная строка запроса
 * Разделяет явные GET-запросы от неявных и возвращает только неявные без слеша '/' в конце
 */
function remove_query_string ($query)
{
    $params = explode('&', $query, 2);
    if (strpos($params[0], '=')) return '';
    return rtrim($params[0],'/');
}

/**
 * @param $array   - Массив элементов, находящихся в буферной таблице
 * @param $user_id - id текущего пользователя
 * @return int     - Возвращает количество элементов, добавленных id-пользователем
 */
function my_count($array, $user_id)
{
    $count = 0;
    foreach ($array as $item){
        if ($item->user_id == $user_id) $count++;
    }
    return $count;
}

/**
 * @return mixed - возвращает ip пользователя
 */
function get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * @return mixed - Возвращает информацию о браузере и системе пользователя
 */
function get_client()
{
    return $_SERVER['HTTP_USER_AGENT'];
}

/**
 * @param int $length - Длина сгенерированного пароля по умолчанию
 * @return string     - Сгенерированный пароль/строка из случайного набора символов
 */
function generatePassword($length = 8){
    $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
    $numChars = strlen($chars);
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= substr($chars, rand(1, $numChars) - 1, 1);
    }
    return $string;
}