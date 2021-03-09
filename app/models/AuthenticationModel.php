<?php
namespace app\models;

/**
 * Created by UnityWorld Framework
 * ClassName AuthenticationController
 * User: Andrei G. Pastushenko
 * Date: 02.12.2K17
 * Time: 23:55
 */
/**
 * Class AuthenticationModel
 * @package app\models
 */
class AuthenticationModel extends AppModel
{
    /**
     * @param $result
     * @return bool
     */
    public function validate($result): bool
    {
        if (!is_null($result['client']) && !is_null($result['ip']) && !is_null($result['session'])){

            if ($result['client'] != get_client() || $result['ip'] != get_ip()){

                return false;

            }
        }
        return true;
    }

    /**
     * @param $result
     */
    public function setSession($result)
    {
        session_start();

        $_SESSION['id']       = $result['id'];
        $_SESSION['login']    = $result['login'];
        $_SESSION['name']     = $result['name'];
        $_SESSION['surname']  = $result['surname'];
        $_SESSION['role_id']  = $result['role_id'];
        $_SESSION['identity'] = $result['password'];
        $_SESSION['session']  = password_hash(generatePassword(), PASSWORD_DEFAULT);
        $_SESSION['location'] = get_ip();
        $_SESSION['client']   = get_client();

        setcookie('user_id', $result['id'], time()+60*60*12);

        unset($_POST['login']);

        if (!empty($_SESSION)) echo true;
    }

    public function socketUserAuth()
    {
        $this->socket->userAuthSession($_SESSION['id'], $_SESSION['session']);
    }
}