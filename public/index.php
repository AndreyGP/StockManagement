<?php
error_reporting(-1);
/**
 * Created by PhpStorm.
 * User: Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 13:40
 */
use wendor\core\Router;

require_once "../wendor/libs/functions.php";
$query = remove_query_string(rtrim($_SERVER['QUERY_STRING'], '/'));

define('WWW', __DIR__);
define('CORE', dirname(__DIR__) . '/wendor/core' );
define('LIBS',  dirname(__DIR__) . '/wendor/libs');
define('ROOT', dirname(__DIR__));
define('APP', dirname(__DIR__) . '/app');
define('VIEW', dirname(__DIR__) . '/app/views');
define('DEV_MODE', dirname(__DIR__) . '/app/views/Elements/DevMode');
define('LAYOUT', 'default');

spl_autoload_register(function ($class){
    $file = ROOT . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) require_once $file;
});

require_once CORE . "/regulations.php";

Router::dispatch($query, $dev);

