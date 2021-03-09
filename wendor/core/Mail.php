<?php
/**
 * Created by UnityWorld Framework.
 * @author: Andrei G. Pastushenko
 * Date: 28.01.2018
 * Time: 23:20
 * FileName: Mail.php
 */

namespace wendor\core;


use wendor\libs\phpmailer\src\PHPMailer;
use wendor\libs\phpmailer\src\Exception;
use wendor\libs\phpmailer\src\SMTP;
require ROOT . "wendor/libs/phpmailer/src/PHPMailer.php";
require ROOT . "wendor/libs/phpmailer/src/Exception.php";
require ROOT . "wendor/libs/phpmailer/src/SMTP.php";

class Mail extends PHPMailer
{
    public $priority = 3;
    public $to_name;
    public $to_email;
    public $From     = null;
    public $FromName = null;
    public $Sender   = null;

    public function FreakMailer()
    {
        $site = require ROOT .  '/config/maildefault.php';

        // Берем из файла maildefault.php массив $site

        if($site['smtp_mode'] == 'enabled')
        {
            $this->Host = $site['smtp_host'];
            $this->Port = $site['smtp_port'];
            if($site['smtp_username'] != '')
            {
                $this->SMTPAuth  = true;
                $this->Username  = $site['smtp_username'];
                $this->Password  =  $site['smtp_password'];
            }
            $this->Mailer = "smtp";
        }
        if(!$this->From)
        {
            $this->From = $site['from_email'];
        }
        if(!$this->FromName)
        {
            $this-> FromName = $site['from_name'];
        }
        if(!$this->Sender)
        {
            $this->Sender = $site['from_email'];
        }
        $this->Priority = $this->priority;
    }
}