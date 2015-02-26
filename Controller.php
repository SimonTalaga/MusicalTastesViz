<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 26/02/15
 * Time: 01:18
 */
require_once 'vendor/echonest/Autoloader.php';
require_once 'vendor/echonest/Autoloader.php';
require_once 'vendor/lastfm/api/lastfmapi.php';
require_once 'ApplicationConfig.php';
require_once 'functions.php';
require_once 'MusicKeys.php';
require_once 'vendor/spotify/src/SpotifyWebAPIException.php';
require_once 'vendor/spotify/src/SpotifyWebAPI.php';
require_once 'vendor/spotify/src/Session.php';
require_once 'vendor/spotify/src/Request.php';

class Controller {

    public function __construct() {

    }

    public function buildGraph() {
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
    }

    public function analyzeTracks() {
        // ---------------- API Instances ---------------------
        // Spotify
        $spotifyApi = new SpotifyWebAPI\SpotifyWebAPI();

        // LastFM
        $authVars['apiKey'] = ApplicationConfig::lastFmKey;
        $auth = new lastfmApiAuth('setsession', $authVars);
        $lastFm = new lastfmApi();
        $lastFmLibrary = $lastFm->getPackage($auth, 'library');

        // Echo Nest
        EchoNest_Autoloader::register();
        $echonest = new EchoNest_Client();
        $echonest->authenticate(ApplicationConfig::echoNestKey);
        $tracksApi = $echonest->getTrackApi();
        $songsApi = $echonest->getSongApi();
        // -----------------------------------------------------

        $userTracks = $lastFmLibrary->getTracks(array('user' => ApplicationConfig::$lastFmUser, 'limit' => 100));
        $userTracks = $userTracks['results'];

        $analyze_md5_data = array();
        $tracks_analysis_data = array();

        if(!file_exists('data/' . ApplicationConfig::$lastFmUser . '-tracks-analysis.json')) {
            // Envoi des données pour analyse
            $i = 0;
            foreach($userTracks as $track) {
                $result = $songsApi->search(array('title' => $track['name'], 'artist' => $track['artist']['name'], 'results' => 1, 'bucket' => array('id:spotify', 'tracks')));

                if(isset($result[0]['tracks'][0]['foreign_id'])) {
                    $spotify_id = substr($result[0]['tracks'][0]['foreign_id'], 14);
                    $track_data = $spotifyApi->getTrack($spotify_id);

                    array_push($tracks_analysis_data, array('name' => $track['name'], 'artist' => $track['artist']['name'], 'sample' => null, 'analysis' => null));
                    echo 'Traitement de '. $track_data->name . '(' . $i .' / 100)<br />';
                    ob_flush();
                    flush();

                    if($track_data->preview_url != null) {
                        $tracks_analysis_data[$i]['sample'] = $track_data->preview_url;
                        try {
                            $analysis = $tracksApi->upload($track_data->preview_url, true, 'mp3', 'audio_summary');

                            if($analysis['track']['md5'] != null) {
                                // S'il n'y a pas de données d'analyse musicale
                                if($analysis['track']['audio_summary']['tempo'] == null) {
                                    echo "Pas d'analyse <br />";
                                    array_push($analyze_md5_data, array('track_id' => $i, 'md5' => $analysis['track']['md5']));
                                }

                                else {
                                    $tracks_analysis_data[$i]['analysis'] = $analysis['track']['audio_summary'];
                                }
                            }

                            else {
                                echo 'Ta mère';
                            }

                        } catch (EchoNest_HttpClient_Exception $e) {
                            echo "Erreur d'Echonest";
                        }
                    }
                    $i++;
                }
                else {
                    echo 'Not found ' . $track['name'] . 'on Spotify. <br />';
                }
            }

            var_dump($tracks_analysis_data);
            var_dump($analyze_md5_data);
            ob_flush();
            flush();

            // Si il existe des analyses en cours chez Echonest, On attend un peu et on récupère les données
            if(!empty($analyze_md5_data)) {
                sleep(10);
                foreach($analyze_md5_data as $data) {
                    if($data['md5'] != null) {
                        $analysis = $tracksApi->profileMd5($data['md5'], 'audio_summary');
                        $tracks_analysis_data[$data['track_id']]['analysis'] = $analysis['track']['audio_summary'];
                    }
                }
            }

            saveJson($tracks_analysis_data, 'data/' . ApplicationConfig::$lastFmUser . '-tracks-analysis.json');
            $this->getFavoritePitch();
        }

        else {
            $this->getFavoritePitch();
        }
    }

    public function getFavoritePitch() {
        $tracks_analysis_data = readJson('data/' . ApplicationConfig::$lastFmUser . '-tracks-analysis.json');
        // Calcul de la tonalité préférée
        $pitches = array();

        foreach($tracks_analysis_data as $data) {
            if($data['analysis'] != null)
                array_push($pitches, MusicKeys::getPitch($data['analysis']['key'], $data['analysis']['mode']));
        }

        $empty_array = array_map( function($v) { return 0; }, $pitches);
        $pitches_count = array_combine($pitches, $empty_array);

        foreach($pitches as $pitch) {
            $pitches_count[$pitch]++;
        }

        arsort($pitches_count);
        $favePitch = array_keys($pitches_count)[0];
        $favePitchDesc = MusicKeys::getPitchDesc($favePitch);

        echo json_encode(array(
            'favoritePitch' => $favePitch,
            'desc' => $favePitchDesc,
            'array' => $pitches_count
        ), JSON_UNESCAPED_UNICODE);
    }
} 