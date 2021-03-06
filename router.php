<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 26/02/15
 * Time: 01:24
 */

require_once 'Controller.php';

$controller = new Controller();

switch($_GET['action']) {
    case 'index':
        $controller->index();
        break;
    case 'buildGraph':
        $controller->buildGraph($_GET['user']);
        break;
    case 'analyzeTracks':
        $controller->analyzeTracks($_GET['user']);
        break;
    case 'getFavoritePitch':
        $controller->getFavoritePitch($_GET['user']);
        break;
    case 'getAcousticTastes':
        $controller->getAcousticTastes($_GET['user']);
        break;
}
