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
    case 'buildGraph':
        $controller->buildGraph();
        break;
    case 'analyzeTracks':
        $controller->analyzeTracks();
        break;
    case 'getFavoritePitch':
        $controller->getFavoritePitch();
        break;
}
