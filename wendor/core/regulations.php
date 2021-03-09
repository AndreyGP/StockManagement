<?php
/**
 * Created by PhpStorm.
 * User: Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 15:43
 */
use wendor\core\Router;
/**
 * Включение/выключение режима разработчика [developer mode]. По умолчанию 'true' - включен developer mode
 */
$dev = true;

/**
 * Правило для '/login'
 */
Router::add('^login/?$', [
    'controller' => 'authentication',
    'action' => 'login',
    'method' => false
]);

/**
 * Правило для '/logout'
 */
Router::add('^logout/?$', [
    'controller' => 'authentication',
    'action' => 'logout',
    'method' => false
]);

/**
 * Правило раутинга для несуществующих index экшенов
 */
Router::add('^index\.?[a-z]+?/?$', [
    'controller' => 'cells',
    'method' => false
]);
/**
 * Правило раутинга для несуществующих index экшенов
 */
Router::add('^mirror/?[a-z]+?\.?[a-z]+?/?$', [
    'controller' => 'mirror',
    'method' => false
]);
/**
 * Правило раутинга для select экшен
 */
Router::add('^cells/select/?$', [
    'controller' => 'cells',
    'method' => 'select'
]);
/**
 * Правило раутинга для numberSearch экшен
 */
Router::add('^cells/number-search/?$', [
    'controller' => 'cells',
    'method' => 'numberSearch'
]);
/**
 * Правило раутинга для freeCell экшен
 */
Router::add('^cells/free-cell/?$', [
    'controller' => 'cells',
    'method' => 'freeCell'
]);
/**
 * Правило раутинга для cellPut экшен
 */
Router::add('^cells/cell-put/?$', [
    'controller' => 'cells',
    'method' => 'cellPut'
]);
/**
 * Правило раутинга для cellsBufferUpdate экшен
 */
Router::add('^cells/cell-buffer-update/?$', [
    'controller' => 'cells',
    'method' => 'cellBufferUpdate'
]);
/**
 * Правило раутинга для cellInsert экшен
 */
Router::add('^cells/cell-insert/?$', [
    'controller' => 'cells',
    'method' => 'cellInsert'
]);
/**
 * Правило раутинга для прочих не index экшенов
 */
Router::add('^'.
    'cells/'.
    '((?P<rack_ab>[AaBb]{1})'.
    '(?P<rack_num>[0-9]{1,2}))?-?'.
    '(?P<row>[1-9]{1})?-?'.
    '(?P<col>[1-9]{1})?'.
    '(?P<cell>[0-9]{1,2})?'.
    '$', [
        'controller' => 'cells',
        'action' => 'view',
        'method' => false
]);


/*DEFAULTS ROUTS*/
/**
 * Правила раутинга при пустом запросе, т.е. индексные контроллер и экшен
 */
Router::add('^$', [
    'controller' => 'cells',
    'action' => 'index',
    'method' => false
]);
/**
 * Правила раутинга для всех контроллеров, экшенов и параметров запроса
 */
Router::add('^'.
    '(?P<controller>[a-z-]+)/?'.
    '(?P<action>[a-z-]+)?/?'.
    '((?P<rack_ab>[AaBb]{1})'.
    '(?P<rack_num>[0-9]{1,2}))?-?'.
    '(?P<row>[1-9]{1})?-?'.
    '(?P<col>[1-9]{1})?'.
    '(?P<cell>[0-9]{1,2})?'.
'$', [
    'method' => false
]);

