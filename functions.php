<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 21/02/15
 * Time: 22:42
 */

/*
function saveWorkingData($data, $filename) {
    $file = fopen($filename, 'w');
    $json = stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));
    fwrite($file, mb_convert_encoding($json, 'UTF-8', 'auto'));
    fclose($file);
}

function readWorkingData($filename) {
    $file = file($filename);
    $data = json_decode($file[0], true);
    unlink($filename);
    return $data;
}
*/

function dataToGraphStructure($data) {
    $nodes = array();

    foreach($data as $artist) {
        array_push($nodes, new Node($artist['name'], $artist['playcount'], $artist['image']['large']));
    }

    return new ArtistsGraph($nodes);
}