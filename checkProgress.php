<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 23/02/15
 * Time: 22:15
 */
session_start();
if(isset($_SESSION['progress'])) {
    echo $_SESSION['progress'];
}

else {
    echo 0;
}