<?php
namespace wendor\core\base;
/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 12.10.2017
 * Time: 19:37
 */

class View
{
    /**
     * @var array - текущий маршрут запроса
     */
    public $route = [];

    /**
     * @var string - текущий вид запроса
     */
    public $view;

    /**
     * @var string - текущий layout запроса, он же default layout
     */
    public $layout;

    /**
     * @var array - передаваемые пользователем в вид данные
     */
    //protected $data = [];

    public function __construct ($route, $layout = '', $view = '')
    {
        $this->route = $route;
        if ($layout !== false){
            ($layout)
                ? $this->layout = $layout . '.tmp'
                : $this->layout = LAYOUT . '.tmp';
            ($view)
                ? $this->view = $view
                : $this->view = $this->route['action'];
        } else {
            $this->layout = false;
        }
    }

    public function render ($data)
    {//debug($data);
        if ($data){
            foreach ($data as $var => $val){
                extract($$var = $val);//debug($$var);
            }
            unset($data);
            $file_view = VIEW . '/' . $this->route['controller'] . '/' . $this->view . '.tmp';
            ob_start();
            (is_file($file_view))
                ? require $file_view
                : not_found();
            $content = ob_get_clean();
            if (false !== $this->layout){
                $file_layouts = VIEW . "/Layouts/{$this->layout}";
                (is_file($file_layouts))
                    ? require_once $file_layouts
                    : not_found();
            }
        }
    }

}