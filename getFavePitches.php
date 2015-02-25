<DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
</head>

<body>
<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 24/02/15
 * Time: 18:40
 */

require 'vendor/echonest/Autoloader.php';
require_once 'vendor/echonest/Autoloader.php';
require_once 'vendor/lastfm/api/lastfmapi.php';
require_once 'ApplicationConfig.php';
require_once 'functions.php';
require_once 'MusicKeys.php';

require_once 'vendor/spotify/src/SpotifyWebAPIException.php';
require_once 'vendor/spotify/src/SpotifyWebAPI.php';
require_once 'vendor/spotify/src/Session.php';
require_once 'vendor/spotify/src/Request.php';

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

$userTracks = $lastFmLibrary->getTracks(array('user' => ApplicationConfig::$lastFmUser, 'limit' => 120));
$userTracks = $userTracks['results'];
$analyze_md5_data = array();

if(!file_exists('data/' . ApplicationConfig::$lastFmUser . '-fave-pitches.json')) {
    // Envoi des données pour analyse
    $i = 0;
    foreach($userTracks as $track) {
        $result = $songsApi->search(array('title' => $track['name'], 'artist' => $track['artist']['name'], 'results' => 1, 'bucket' => array('id:spotify', 'tracks')));

        if(isset($result[0]['tracks'][0]['foreign_id'])) {
            $spotify_id = substr($result[0]['tracks'][0]['foreign_id'], 14);
            $track_data = $spotifyApi->getTrack($spotify_id);

            echo 'Traitement de '. $track_data->name . '(' . $i .' / 100)<br />';
            ob_flush();
            flush();

            if($track_data->preview_url != null) {
                try {
                    $track_analyze = $tracksApi->upload($track_data->preview_url, true, 'mp3', 'audio_summary');
                    if($track_analyze['track']['md5'] != null)
                        array_push($analyze_md5_data, $track_analyze['track']['md5']);

                } catch (EchoNest_HttpClient_Exception $e) {
                    echo "Erreur d'echonest";
                }
            }
        }
        $i++;
    }

    sleep(10);

    // Récupération des données d'analyse
    $pitches = array();
    foreach($analyze_md5_data as $md5) {
        if($md5 != null) {
            $track_analyze = $tracksApi->profileMd5($md5, 'audio_summary');
        }
        //var_dump($track_analyze);
        array_push($pitches, MusicKeys::getPitch($track_analyze['track']['audio_summary']['key'], $track_analyze['track']['audio_summary']['mode']));
    }

    $empty_array = array_map( function($v) { return 0; }, $pitches);
    $pitches_count = array_combine($pitches, $empty_array);

    foreach($pitches as $pitch) {
        $pitches_count[$pitch]++;
    }

    arsort($pitches_count);
    $favePitch = array_keys($pitches_count)[0];
    $favePitchDesc = MusicKeys::getPitchDesc($favePitch);

    echo 'Tonalité préférée : ' . $favePitch . ' : ' . $favePitchDesc;
    var_dump($pitches_count);

    saveJson($pitches_count, 'data/' . ApplicationConfig::$lastFmUser . '-fave-pitches.json');
}

else {
    var_dump(readJson('data/' . ApplicationConfig::$lastFmUser . '-fave-pitches.json'));
}
?>
</body>
</html>

