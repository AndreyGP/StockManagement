<?php
namespace app\controllers;
use app\models\CellsModel;

/**
 * Created by PhpStorm.
 * @author Andrei G. Pastushenko
 * Date: 08.10.2017
 * Time: 21:40
 */

class CellsController extends AppController
{

    /**
     *
     */
    public function indexMethod()
    {
        session_start();
        if (!isset($_COOKIE ['session' ])                  ||
            !isset($_SESSION['session' ])                  ||
            !isset($_SESSION['location'])                  ||
            !isset($_SESSION['client'  ])                  ||
            $_SESSION['location']   != get_ip    ()        ||
            $_SESSION['client'  ]   != get_client()        ||
            $_SESSION['session' ]   != $_COOKIE['session'] ){

            $host    = $_SERVER['HTTP_HOST'];
            $extra   = 'login';

            header("Location: https://$host" /* . $uri*/ . "/$extra");
            exit;
        }

        $this->title = 'Размещение по местам';

        $model       = new CellsModel();

        $res         = $model->findAll();
        $buff        = $model->findAll('cells_boofer');

        (empty($buff))
            ? $c_up = false
            : $c_up = true;

        $this->set(compact('res', 'c_up'));
        $this->getDisplay();
    }

    /**
     *
     */
    public function bufferMethod()
    {

        session_start();
        if (!isset($_COOKIE ['session' ])                  ||
            !isset($_SESSION['session' ])                  ||
            !isset($_SESSION['location'])                  ||
            !isset($_SESSION['client'  ])                  ||
            $_SESSION['location']   != get_ip    ()        ||
            $_SESSION['client'  ]   != get_client()        ||
            $_SESSION['session' ]   != $_COOKIE['session'] ){

            $host             = $_SERVER['HTTP_HOST'];
            $extra            = 'login';

            header("Location: https://$host" /* . $uri*/ . "/$extra");
            exit;
        }

        ($_COOKIE['user_id'] == $_SESSION['id'])
            ? $user_id = (int)$_SESSION['id']
            : $user_id = false;

        $model                = new CellsModel();

        $res                  = $model->findAll('cells_boofer', ' ORDER BY p_numb ASC ');
        $users                = $model->findAll('users');

        $count_all            = count($res);
        $my_count             = my_count($res, $user_id);
        $checkbox             = $count_all - $my_count;

        if ($count_all == 0){
            $host             = $_SERVER['HTTP_HOST'];
            //$extra            = 'login';
            header("Location: https://$host" /* . $uri . "/$extra"*/);
            exit;
        }

        setcookie('count_all', $count_all);
        setcookie('my_count', $my_count);

        $this->title          = 'Работа с 1С';

        $this->set(compact('res', 'user_id', 'users', 'checkbox'));
        $this->getDisplay();
    }

    /**
     * @return bool
     */
    public function select()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            $model        = new CellsModel();

            $ajax         = $_POST;

            switch ($ajax['type']):

                case 'rack':

                    $data = $model->findAll($ajax['value']);

                    $data = json_encode($data);

                    echo $data;
                    break;

                case 'row':

                    $data = $model->load($ajax['table'], $ajax['value']);

                    $data = json_encode($data);

                    echo $data;
                    break;

                default:

                    return false;
                    break;

            endswitch;
        endif;
        return false;
    }

    /**
     *
     */
    public function numberSearch()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            $model      = new CellsModel();

            $ajax       = mb_strtoupper($_POST['p_num']);

            $result     = $model->findColumn('cells', 'p_numb', ["{$ajax}"]);

            if ($result == null)
                $result = $model->findColumn('cells_boofer', 'p_numb', ["{$ajax}"]);

            echo json_encode($result);
        endif;
        //return false;
    }

    /**
     * @return bool | array
     */
    public function freeCell()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            session_start();

            $model     = new CellsModel();

            $letter    = mb_strtoupper($_POST['letter']);

            $rack      = (int)$_POST['rack'];

            if ($rack <= 9)
                $rack = '0' . strval($rack);

            $table     = $letter . $rack;

            $row       = $_POST['row'];
            $col       = $_POST['col'];

            $sql_query =
                "SELECT id, name, row, col, cell " .
                "FROM $table " .
                "WHERE row  = :row " .
                "AND col  = :col " .
                "AND free = :free " .
                "ORDER BY cell ASC " .
                "LIMIT 1";

            $sql_params = [
                ":row"  => $row,
                ":col"  => $col,
                ":free" => '1'
            ];

            $result = $model->sqlQuery($sql_query, $sql_params);

            if ($result && (int)$result['cell'] <= 9)
                $result['cell'] = '0' . strval($result['cell']);


            //$model->cellUpdateToBufferPageFromSocket("one, two, {$_SESSION['id']}, close_cell");

            session_write_close();

            echo json_encode($result);
        endif;

        return false;
    }

    /**
     *
     */
    public function cellPut()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            session_start();
            ($_COOKIE['user_id'] == $_SESSION['id'])
                ? $user_id = $_SESSION['id']
                : $user_id = $_COOKIE['user_id'];

            $id                   = $_POST['id'       ];
            $table                = $_POST['table'    ];
            $cell_full            = $_POST['cell_full'];

            $part_num             = mb_strtoupper($_POST['part_num']);
            $time                 = date('Y-m-d H:i:s', time());

            $model                = new CellsModel();

            $sql_update           = "UPDATE $table SET free = 0 WHERE id = $id";

            $params               = [
                'cell'            => "$cell_full"  ,
                'p_num'           => "$part_num"   ,
                'user_id'         => "$user_id"    ,
                'table'           => 'cells_boofer',
                'timestamp'       =>  $time
            ];

            if (($model->insert($params)) === false){
                echo false;
                exit;
            }
            if (!$model->update($sql_update)){
                echo false;
                exit;
            }

            $users                = $model->findAll('users');
            $user_this            = $users[$user_id];
            $user_s_n             = $user_this->name . ' ' . $user_this->surname;

            $result               = $model->findColumn('cells_boofer', 'p_numb', ["$part_num"]);
            $insert_id            = $result['id'];

            $json                 = "$insert_id, $part_num, $cell_full, $user_s_n";

            $model->insertNewCellToBufferPageFromSocket($json);

            echo  true;

        endif;
    }

    /**
     *
     */
    public function cellBufferUpdate()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):


            session_start();

            $id_cell         = $_POST['id_cell'  ];
            $id_buffer       = $_POST['id_buffer'];
            $table           = $_POST['table'    ];
            $cell_full       = $_POST['cell_full'];
            $buffer          = 'cells_boofer'     ;

            $model           = new CellsModel();

            $sql_update_rack = "UPDATE $table SET free = 0 WHERE id = $id_cell";
            $sql_update_buff = "UPDATE $buffer SET cells = '$cell_full' WHERE id = $id_buffer";

            if (!$model->update($sql_update_rack)){
                echo false;
                exit;
            }
            if (!$model->update($sql_update_buff)){
                echo $sql_update_buff;
                exit;
            }

            $model->cellUpdateToBufferPageFromSocket("$id_buffer, $cell_full, {$_SESSION['id']}, open_cell");

            session_write_close();

            echo  true;
        endif;
    }

    /**
     *
     */
    public function cellInsert()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
            ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            session_start();

            $model      = new CellsModel();

            $id         = $_POST['id'];
            $buffer_row = $model->load('cells_boofer', $id);

            $cell       = $buffer_row['cells'  ];
            $part_num   = $buffer_row['p_numb' ];
            $user_id    = $buffer_row['user_id'];
            $time       = $buffer_row['dates'  ];

            $params     = [
                'cell'      => "$cell"    ,
                'p_num'     => "$part_num",
                'user_id'   => "$user_id" ,
                'table'     => 'cells'    ,
                'timestamp' =>  $time
            ];

            if (!$model->insert($params)){
                echo false;
                exit;
            }

            $model->rowDelete($buffer_row);

            $model->deleteDivCellToBufferPageFromSocket("$id, {$_SESSION['id']}");

            session_write_close();

            echo true;
        endif;
    }
}