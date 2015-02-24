<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 23/02/15
 * Time: 22:22
 */
require_once 'vendor/echonest/Autoloader.php';
require_once 'vendor/lastfm/api/lastfmapi.php';
require_once 'ApplicationConfig.php';
require_once 'Node.php';
require_once 'Edge.php';
require_once 'ArtistsGraph.php';
require_once 'functions.php';

if(isset($_POST['user'])) {
    ApplicationConfig::$lastFmUser = $_POST['user'];
}

if(!file_exists('data/' . ApplicationConfig::$lastFmUser . '.json'))
{
    EchoNest_Autoloader::register();
    $params = array('user' => ApplicationConfig::$lastFmUser, 'limit' => '50');

    // Auth to LastFM API
    $authVars['apiKey'] = ApplicationConfig::lastFmKey;
    $auth = new lastfmApiAuth('setsession', $authVars);

    // API Instances
    $lastFm = new lastfmApi();
    $lastFmUser = $lastFm->getPackage($auth, 'user');
    $lastFmLibrary = $lastFm->getPackage($auth, 'library');

    $tracks = $lastFmLibrary->getArtists($params);
    if($tracks) {
        $totalPages = (int)$tracks['totalPages'];
        $result = Array();

        for($i = 1; $i <= $totalPages; $i++) {
            $query = array('user' => ApplicationConfig::$lastFmUser, 'page' => $i, 'limit' => '50');
            $tracks = $lastFmLibrary->getArtists($query);
            for($j = 0; $j < count($tracks['results']); $j++) {
                array_push($result, $tracks['results'][$j]);
            }
        }

        // Auth to EchoNest API
        $echonest = new EchoNest_Client();
        $echonest->authenticate(ApplicationConfig::echoNestKey);
        $artistApi = $echonest->getArtistApi();
        $artistsGraph = dataToGraphStructure($result);
        var_dump($result);

        // "Moulinette" qui récupère les artistes similaires selon une profondeur de similarDepth (nombre d'artistes liés par artiste)
        // et met à jour le tableau avec les artistes liés entre eux dans la bibliothèque
        $i = 1;

        session_start();
        $_SESSION['total'] = count($artistsGraph->getNodes());
        session_write_close();

        foreach($artistsGraph->getNodes() as $node) {
            // On stocke la progression actuelle
            session_start();
            $_SESSION['progress'] = ceil($i / $_SESSION['total'] * 100);
            session_write_close();

            try {
                $artistApi->setName($node->label);
                $similars = array_column($artistApi->getSimilar(ApplicationConfig::similarDepth, 1), 'name');

                foreach($similars as $similar) {
                    $similar_id = $artistsGraph->searchArtist($similar);
                    if($similar_id && !$artistsGraph->searchLink($similar_id, $node->id)) {
                        $artistsGraph->addEdge(new Edge($node->id, $similar_id));
                    }
                }
            } catch(Exception $e) { echo 'Artiste inconnu : '. $node->label .'<br />'; };
            $i++;
        }

        session_start();
        $_SESSION['progress'] = 0;
        session_write_close();

        $artistsGraph->saveToJSON('data/' . ApplicationConfig::$lastFmUser . '.json');
    }

    else {
        die($lastFmLibrary->error['desc']);
    }
}
