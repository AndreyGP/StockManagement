<?php
namespace wendor\core\base;
/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 22:00
 */

abstract class Controller
{
    /**
     * @var array - текущий маршрут запроса
     */
    public $route = [];
    /**
     * @var string - используемый по умолчанию шаблон
     */
    public $layout;
    /**
     * @var string - используемый вид
     */
    public $view;
    /**
     * @var string - Title текущей страницы
     */
    public $title = '';
    /**
     * @var array - пользовательские данные
     */
    public $data  = [];

    /**
     * AppController constructor.
     * @param $route - передавемый "роутером" текущий маршрут запроса
     * Обрабатывает текущий запрос и организовывает подключение необходимых вида и шаблона
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     *
     *
     */
    public function getDisplay()
    {
        $vObj = new View($this->route, $this->layout, $this->view);
        $vObj->render($this->data);
    }

    public function set (array $data)
    {
        if (is_array($data)) {
            $this->data['data']   = $data;
            $this->data['option'] = ['title' => $this->title];
        }
    }
}
