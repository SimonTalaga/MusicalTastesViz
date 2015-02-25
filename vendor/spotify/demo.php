<?php
error_reporting(-1);
ini_set('display_errors', 1);

require_once 'src/Request.php';
require_once 'src/Session.php';
require_once 'src/SpotifyWebAPI.php';
require_once 'src/SpotifyWebAPIException.php';


$session = new SpotifyWebAPI\Session('23f41673f5504095806903b1cbe50ce4', 'ac2f6be2ed60440d9bad18018d133dbb', 'http://localhost/MusicTastesViz/vendor/spotify/demo.php');
$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['code'])) {
    $session->requestToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());

    print_r($api->me());
} else {
    header('Location: ' . $session->getAuthorizeUrl(array(
        'scope' => array('user-read-email', 'user-library-modify')
    )));
}
