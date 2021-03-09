<?php
namespace wendor\core;
/**
 * Created by PhpStorm.
 * User: Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 14:50
 */

/**
 * Class Router
 * Осуществляет общие логику и обработку ЧПУ URL
 * Получает "грязную" строку запроса и отдаёт ассоциативный массив с параметрами запроса "controller", "action" и
 * необходимые параметры, если таковые имеются в строке запроса. Сравнение осуществляется путём регулярных выражений,
 * заданных в файле с правилами раутинга regulation.php
 */
class Router
{
    /**
     * @routes array - содержит все пути строки запроса
     */
    protected static $routes = [];
    /**
     * @route array - содержит текущий путь строки запроса
     */
    protected static $route = [];

    /**
     * @param $regexp - текущая строка запроса
     * @param array $route - текущий путь в виде параметров пути запроса
     */
    public static function add($regexp, $route = [])
    {
        self::$routes[$regexp] = $route;
    }

    /**
     * @return array
     * Тестовый экшен для получения списка путей запроса
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * @return array
     * Тестовый экшен для получения текущего пути запроса
     */
    public static function getRoute()
    {
        return self::$route;
    }

    /**
     * @param $url - текущая строка запроса
     * @return bool
     * Получает текущую строку запроса, сравнивает с правилами раутинга и разбивает совпадения на ключ/значение массив,
     * для дальнейшего его использования.
     */
    private static function matchRoute($url)
    {
        foreach (self::$routes as $pattern => $route){
            if (preg_match("#$pattern#i", $url, $matches)){
                foreach ($matches as $key => $value){
                    if (is_string($key)) $route[$key] = strtolower($value);
                }
                if ( !isset($route['action']) || empty($route['action']) ) $route['action'] = 'index';
                $route['controller'] = upper_camel_case($route['controller']);
                $route['action'] = lower_camel_case($route['action']);
                self::$route = $route;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $url - входящий URL
     * @param $mode - режим отображения
     * Перенаправляет URL по корректному маршруту, а так же отрабатывает режим разработки
     */
    public static function dispatch($url, $mode)
    {
        if ($url == 'index') $url = 'cells';
        if ( !self::matchRoute($url)  ) not_found();

        $controller = self::$route['controller'] . 'Controller';
        $action     = self::$route['action'] . 'Method';
        $method     = self::$route['method'];
        if (!empty($method)) $action = $method;
        ($mode)
            ? $obj  = dev_mode($controller, $action, self::$route)
            : $obj  = object_controller_factory($controller, self::$route);

        (method_exists($obj, $action))
            ? $obj->$action()
            : not_found();
        $obj->getDisplay();
    }

    /**
     * Подключение вида страницы
     */
    function render ()
    {
        include_once VIEW . '/' . $this->route['controller'] . '/' . $this->route['action'] . '.tmp';
    }

}




















