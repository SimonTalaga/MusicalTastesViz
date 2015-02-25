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

$userTracks = $lastFmLibrary->getTracks(array('user' => ApplicationConfig::$lastFmUser, 'limit' => 60));
$userTracks = $userTracks['results'];
$analyze_md5_data = array();

if(!file_exists('data/' . ApplicationConfig::$lastFmUser . '-tracks.json')) {
    foreach($userTracks as $track) {
        $result = $songsApi->search(array('title' => $track['name'], 'artist' => $track['artist']['name'], 'results' => 1, 'bucket' => array('id:spotify', 'tracks')));

        if(isset($result[0]['tracks'][0]['foreign_id'])) {
            $spotify_id = substr($result[0]['tracks'][0]['foreign_id'], 14);
            $track_data = $spotifyApi->getTrack($spotify_id);
            $track_data->preview_url;

            if($track_data->preview_url != null) {
                try {
                    $track_analyze = $tracksApi->upload($track_data->preview_url, true, 'mp3', 'audio_summary');
                    if($track_analyze['track']['md5'] == null)
                        var_dump($track_analyze);

                    array_push($analyze_md5_data, $track_analyze['track']['md5']);
                } catch (EchoNest_HttpClient_Exception $e) {
                    echo "Erreur d'echonest";
                }
            }
        }
    }
    var_dump($analyze_md5_data);
    saveJson($analyze_md5_data, 'data/' . ApplicationConfig::$lastFmUser . '-tracks.json');
}

else {
    $pitches = array();
    $analyze_md5_data = readJson('data/' . ApplicationConfig::$lastFmUser . '-tracks.json');
    foreach($analyze_md5_data as $md5) {
        $track_analyze = $tracksApi->profileMd5($md5, 'audio_summary');
        //var_dump($track_analyze);
        try {
            array_push($pitches, MusicKeys::getPitch($track_analyze['track']['audio_summary']['key'], $track_analyze['track']['audio_summary']['mode']));
        } catch(Exception $e) {echo $track_analyze['track']['audio_summary']['key']. ' '. $track_analyze['track']['audio_summary']['mode']; }

    }
    var_dump($pitches);

    $lamda = function($value) {
        return 0;
    };

    $pitches_values = array_map($lamda, $pitches);
    $pitches_count = array_combine($pitches, $pitches_values);
    foreach($pitches as $pitch) {
        $pitches_count[$pitch]++;
    }

    var_dump($pitches_count);
}
?>
</body>
</html>

