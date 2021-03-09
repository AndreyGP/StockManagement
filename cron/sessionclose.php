<?php
ini_set('session.gc_m<?php');
/**
 * Created by UnityWorld Framework.
 * User: Andrei G. Pastushenko
 * Date: 25.12.2017
 * Time: 21:39
 */

//ini_set('session.save_path', '/var/www/stockirb/data/sessions/');
ini_set('session.gc_maxlifetime', 1);

$db        = mysqli_connect(
                'localhost',
                'stockworcker',
                'X2x6G3a1',
                'irbis');

$date_time = date('Y-m-d H:i:s');

$sql       = "UPDATE `users` SET `session` = NULL, `ip` = NULL, `client` = NULL, `last_time` = '{$date_time}'";

$res       = mysqli_real_query($db, $sql);

if ($res){
    session_start();
    session_unset();
    session_destroy();
    session_write_close();
}

if (file_exists('/var/www/stockirb/data/sessions')):
    $array = [];
    $array = scandir('/var/www/stockirb/data/sessions');
    $i     = 0;
    foreach ($array as $name):
        if ($name === '.' || $name === '..'){
            unset($array[$i]);
            $i++;
        }
    endforeach;
    if (is_array($array) && !empty($array)):
        foreach (glob('/var/www/stockirb/data/sessions/*') as $file) unlink($file);
    endif;
endif;

mysqli_close($db);