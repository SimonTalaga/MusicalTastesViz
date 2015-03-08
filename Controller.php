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
require_once 'Node.php';
require_once 'Edge.php';
require_once 'ArtistsGraph.php';
require_once 'functions.php';
require_once 'MusicKeys.php';
require_once 'vendor/spotify/src/SpotifyWebAPIException.php';
require_once 'vendor/spotify/src/SpotifyWebAPI.php';
require_once 'vendor/spotify/src/Session.php';
require_once 'vendor/spotify/src/Request.php';
require_once 'vendor/autoload.php';

class Controller {

    private $twig;

    public function __construct() {
        $loader = new Twig_Loader_Filesystem(__DIR__);
        $this->twig = new Twig_Environment($loader, array(
            'cache' => false,
        ));
    }

    public function index() {
        // Récupère toutes les données d'analyse existantes
        $users_data = array('users' => array());
        foreach(scandir('data') as $file) {
            if($file != '.' && $file != '..') {
                array_push($users_data['users'], array('username' => $file));
            }
        }

        echo $this->twig->render('index.html.twig', $users_data);
    }

    public function saveUserPicture($user) {

    }

    public function buildGraph($user) {

        ApplicationConfig::$lastFmUser = $user;

        if(!file_exists('data/' . $user . '/artists-graph.json'))
        {
            mkdir('data/' . $user);
            EchoNest_Autoloader::register();
            $params = array('user' => ApplicationConfig::$lastFmUser, 'limit' => '50');

            // Auth to LastFM API
            $authVars['apiKey'] = ApplicationConfig::lastFmKey;
            $auth = new lastfmApiAuth('setsession', $authVars);

            // API Instances
            $lastFm = new lastfmApi();

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
                $_SESSION['step'] = "Analyse des artistes : ";
                session_write_close();

                foreach($artistsGraph->getNodes() as $node) {
                    // On stocke la progression actuelle
                    session_start();
                    $_SESSION['progress'] = $i;
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

                $artistsGraph->saveToJSON('data/' . $user . '/artists-graph.json');
            }

            else {
                die($lastFmLibrary->error['desc']);
            }
        }
    }

    public function analyzeTracks($user) {
        ApplicationConfig::$lastFmUser = $user;
        header('Content-Type: text/html; charset=utf-8');
        session_start();
        $_SESSION['progress'] = 0;
        session_write_close();
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

        $userTracks = $lastFmLibrary->getTracks(array('user' => ApplicationConfig::$lastFmUser, 'limit' => 50));
        $userTracks = $userTracks['results'];

        $analyze_md5_data = array();
        $tracks_analysis_data = array();

        if(!file_exists('data/' . $user . '/tracks-analysis.json')) {

            session_start();
            $_SESSION['total'] = count($userTracks);
            $_SESSION['step'] = "Analyse des musiques préférées : ";
            session_write_close();
            //Envoi des données pour analyse
            $progress_i = 1;
            $i = 0;
            foreach($userTracks as $track) {
                session_start();
                $_SESSION['progress'] = $progress_i;
                session_write_close();

                $result = $songsApi->search(array('title' => $track['name'], 'artist' => $track['artist']['name'], 'results' => 1, 'bucket' => array('id:spotify', 'tracks')));

                if(isset($result[0]['tracks'][0]['foreign_id'])) {
                    $spotify_id = substr($result[0]['tracks'][0]['foreign_id'], 14);
                    $track_data = $spotifyApi->getTrack($spotify_id);

                    array_push($tracks_analysis_data, array('name' => $track['name'], 'artist' => $track['artist']['name'], 'playcount' => $track['playcount'], 'sample' => null, 'analysis' => null));
                    echo 'Traitement de '. $track_data->name . '(' . $i .' / 100)<br />';
                    echo '<b>'. $track_data->preview_url . '</b><br/>';
                    echo 'jouée ' . $track['playcount'] . 'fois'.
                    ob_flush();
                    flush();

                    if($track_data->preview_url != null) {
                        $tracks_analysis_data[$i]['sample'] = $track_data->preview_url;
                        try {
                            $analysis = $tracksApi->upload($track_data->preview_url, true, 'mp3', 'audio_summary');

                            // On vérifie que l'analyse s'est bien passée
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
                                var_dump($analysis);
                                throw new EchoNest_HttpClient_Exception("Problème avec l'analyseur.");
                            }

                        } catch (EchoNest_HttpClient_Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                    $i++;
                }
                else {
                    echo 'Not found ' . $track['name'] . 'on Spotify. <br />';
                }
                $progress_i++;
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

            saveJson($tracks_analysis_data, 'data/' . $user . '/tracks-analysis.json');
            $this->getFavoritePitch($user);
        }

        else {
            $this->getFavoritePitch($user);
        }
    }

    public function getFavoritePitch($user) {
        $tracks_analysis_data = readJson('data/' . $user . '/tracks-analysis.json');
        if(!empty($tracks_analysis_data)) {
            // Calcul de la tonalité préférée
            $pitches = array();

            foreach($tracks_analysis_data as $data) {
                if($data['analysis'] != null) {
                    for($i = 0; $i < (int)$data['playcount']; $i++) {
                        array_push($pitches, MusicKeys::getPitch($data['analysis']['key'], $data['analysis']['mode']));
                    }
                }
            }

            $empty_array = array_map( function($v) { return 0; }, $pitches);
            $pitches_count = array_combine($pitches, $empty_array);

            foreach($pitches as $pitch) {
                $pitches_count[$pitch]++;
            }

            arsort($pitches_count);

            if(array_values($pitches_count)[0] == array_values($pitches_count)[1] && array_keys($pitches_count)[0] == "Do♯ Majeur")
                $favePitch = array_keys($pitches_count)[1];
            else
                $favePitch = array_keys($pitches_count)[0];

            $favePitchDesc = MusicKeys::getPitchDesc($favePitch);

            echo json_encode(array(
                'favoritePitch' => $favePitch,
                'key' => explode(' ', $favePitch)[0],
                'mode' => explode(' ', $favePitch)[1],
                'desc' => $favePitchDesc,
                'array' => $pitches_count
            ), JSON_UNESCAPED_UNICODE);
        }

        else {
            echo 'Erreur, données vides';
        }
    }

    public function getAcousticTastes($user) {
        $acousticTastes = array(
          'danceability' => 0,
          'energy'       => 0,
          'speechiness'  => 0,
          'acousticness' => 0,
          'valence'      => 0
        );

        $data = readJson('data/' . $user . '/tracks-analysis.json');
        $totalPlays = 0;
        foreach($data as $track) {
            if($track['analysis'] != null) {
                $acousticTastes['danceability'] += $track['analysis']['danceability'] * (int)$track['playcount'];
                $acousticTastes['energy'] += $track['analysis']['energy'] * (int)$track['playcount'];
                $acousticTastes['speechiness'] += $track['analysis']['speechiness'] * (int)$track['playcount'];
                $acousticTastes['acousticness'] += $track['analysis']['acousticness'] * (int)$track['playcount'];
                $acousticTastes['valence'] += $track['analysis']['valence'] * (int)$track['playcount'];
                $totalPlays += (int)$track['playcount'];
            }
        }

        foreach($acousticTastes as $k => &$v) {
            $v = (float)$v / $totalPlays;
        }

        echo json_encode($acousticTastes);
    }
} 