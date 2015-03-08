<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 23/02/15
 * Time: 22:15
 */
session_start();
if(isset($_SESSION['progress'])) {
    echo json_encode(array('step' => $_SESSION['step'], 'progress' => $_SESSION['progress'], 'total' => $_SESSION['total']));
}

else {
    echo 0;
}