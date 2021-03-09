<?php
namespace app\controllers;
use app\models\AuthenticationModel;
/**
 * Created by UnityWorld Framework
 * ClassName AuthenticationController
 * User: Andrei G. Pastushenko
 * Date: 02.12.2K17
 * Time: 23:53
 */

/**
 * Class AuthenticationController
 * @package app\controllers
 */

class AuthenticationController extends AppController
{
    /**
     *
     */
    public function loginMethod()
    {
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower
                  ($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ):

            session_start();

            $model = new AuthenticationModel();

            if (isset($_POST['login'])    && !empty($_POST['login'])
                                          && preg_match("/^[a-z]{3,}_[a-z]{3,}$/i", $_POST['login'])):

                $login  = trim(addslashes(htmlspecialchars(strip_tags($_POST['login']))));
                $result = $model->findColumn('users', 'login', ["{$login}"]);

                if (!$model->validate($result)){
                    echo 'SessionError';
                    exit;
                }

                if ($result != null && is_null($result['session'])){
                    $model->setSession($result);
                }

            endif;

            if (isset($_POST['password']) && !empty($_POST['password'])):

                $password               = trim(addslashes(htmlspecialchars(strip_tags($_POST['password']))));

                if (password_verify($password, $_SESSION['identity']) && $_POST['user_id'] == $_SESSION['id']){

                    unset($_POST, $_SESSION['identity'], $password);

                    $date_time          = date('Y-m-d H:i:s');

                    $sql_session_update = "UPDATE `users` 
                                           SET    `session`   = '{$_SESSION['session' ]}',
                                                  `ip`        = '{$_SESSION['location']}',
                                                  `client`    = '{$_SESSION['client'  ]}',
                                                  `last_time` = '{$date_time}'
                                           WHERE  `id`        =  {$_SESSION['id'      ]}";

                    if ($model->update($sql_session_update)){

                        setcookie('session', $_SESSION['session'], time()+60*60*12, '/');

                        $model->socketUserAuth();

                        echo true; exit;
                    }

                }

            endif;

            session_write_close();

        else:
            $this->title = 'Вход в систему';
            $param       = ['login' => true];
            $this->set($param);
            $this->getDisplay();
        endif;
        echo false;
    }

    public function logoutMethod()
    {

        session_start();

        $model = new AuthenticationModel();

        $date_time         = date('Y-m-d H:i:s');

        $sql_close_session = "UPDATE `users` 
                              SET    `session`   = NULL ,
                                     `ip`        = NULL ,
                                     `client`    = NULL ,
                                     `last_time` = '{$date_time}'
                              WHERE  `id`        =  {$_SESSION['id']}";
        if ($model->update($sql_close_session)){

            setcookie(session_name(),false,time() - 3600,'/');
            session_unset();
            session_destroy();
            session_write_close();
            //session_regenerate_id(true);
            setcookie('session', false, time() - 3600*24, '/');
            setcookie('user_id', false, time() - 3600*24, '/');

            $host          = $_SERVER['HTTP_HOST'];
            $extra         = 'login';

            header("Location: https://$host" /* . $uri*/ . "/$extra");
            exit;
        }
    }
}