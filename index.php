<?php
require_once 'vendor/echonest/Autoloader.php';
require_once 'vendor/lastfm/api/lastfmapi.php';
require_once 'ApplicationConfig.php';
require_once 'Node.php';
require_once 'Edge.php';
require_once 'ArtistsGraph.php';
require_once 'functions.php';


EchoNest_Autoloader::register();
$params = array('user' => ApplicationConfig::lastFmUser, 'limit' => '50');

// Auth to LastFM API
$authVars['apiKey'] = ApplicationConfig::lastFmKey;
$auth = new lastfmApiAuth('setsession', $authVars);

// API Instances
$lastFm = new lastfmApi();
$lastFmUser = $lastFm->getPackage($auth, 'user');
$lastFmLibrary = $lastFm->getPackage($auth, 'library');

$tracks = $lastFmLibrary->getArtists($params);
$totalPages = (int)$tracks['totalPages'];
$result = Array();

for($i = 1; $i <= $totalPages; $i++) {
    $query = array('user' => ApplicationConfig::lastFmUser, 'page' => $i, 'limit' => '50');
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

// "Moulinette" qui récupère les artistes similaires selon une profondeur de $relationshipDepth (nombre d'artistes liés par artiste)
// et met à jour le tableau avec les artistes liés entre eux dans la bibliothèque
$i = 1;
foreach($artistsGraph->getNodes() as $node) {
    // On affiche l'état courant à l'écran
    echo 'Traitement de l\'artiste ' . $i . ' / ' . count($artistsGraph->getNodes()) . '<br />';
    ob_flush();
    flush();

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

$artistsGraph->saveToJSON('data/' . ApplicationConfig::lastFmUser . '.json');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <!-- START SIGMA IMPORTS -->
    <script src="vendor/sigmajs/src/sigma.core.js"></script>
    <script src="vendor/sigmajs/src/conrad.js"></script>
    <script src="vendor/sigmajs/src/utils/sigma.utils.js"></script>
    <script src="vendor/sigmajs/src/utils/sigma.polyfills.js"></script>
    <script src="vendor/sigmajs/src/sigma.settings.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.dispatcher.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.configurable.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.graph.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.camera.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.quad.js"></script>
    <script src="vendor/sigmajs/src/classes/sigma.classes.edgequad.js"></script>
    <script src="vendor/sigmajs/src/captors/sigma.captors.mouse.js"></script>
    <script src="vendor/sigmajs/src/captors/sigma.captors.touch.js"></script>
    <script src="vendor/sigmajs/src/renderers/sigma.renderers.canvas.js"></script>
    <script src="vendor/sigmajs/src/renderers/sigma.renderers.webgl.js"></script>
    <script src="vendor/sigmajs/src/renderers/sigma.renderers.svg.js"></script>
    <script src="vendor/sigmajs/src/renderers/sigma.renderers.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/webgl/sigma.webgl.nodes.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/webgl/sigma.webgl.nodes.fast.js"></script>
    <script src="vendor/sigmajs/src/renderers/webgl/sigma.webgl.edges.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/webgl/sigma.webgl.edges.fast.js"></script>
    <script src="vendor/sigmajs/src/renderers/webgl/sigma.webgl.edges.arrow.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.labels.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.hovers.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.nodes.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edges.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edges.curve.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edges.arrow.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edges.curvedArrow.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edgehovers.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edgehovers.curve.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edgehovers.arrow.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.edgehovers.curvedArrow.js"></script>
    <script src="vendor/sigmajs/src/renderers/canvas/sigma.canvas.extremities.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.utils.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.nodes.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.edges.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.edges.curve.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.labels.def.js"></script>
    <script src="vendor/sigmajs/src/renderers/svg/sigma.svg.hovers.def.js"></script>
    <script src="vendor/sigmajs/src/middlewares/sigma.middlewares.rescale.js"></script>
    <script src="vendor/sigmajs/src/middlewares/sigma.middlewares.copy.js"></script>
    <script src="vendor/sigmajs/src/misc/sigma.misc.animation.js"></script>
    <script src="vendor/sigmajs/src/misc/sigma.misc.bindEvents.js"></script>
    <script src="vendor/sigmajs/src/misc/sigma.misc.bindDOMEvents.js"></script>
    <script src="vendor/sigmajs/src/misc/sigma.misc.drawHovers.js"></script>
    <!-- END SIGMA IMPORTS -->
    <script src="vendor/sigmajs/plugins/sigma.parsers.json/sigma.parsers.json.js"></script>
    <script src="vendor/sigmajs/plugins/sigma.layout.forceAtlas2/supervisor.js"></script>
    <script src="vendor/sigmajs/plugins/sigma.layout.forceAtlas2/worker.js"></script>
    <style>
        #graph-container {
            width: 1000px;
            height: 500px;
            background-color: #eee;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="graph-container"></div>
    </div>
    <script>
        var s = new sigma({
            container: 'graph-container',
            renderer: {
              container: document.getElementById('graph-container'),
                type: 'canvas'
            },
            settings: {
                defaultLabelSize: 20,
                defaultEdgeType: 'curve',
                minNodeSize: 1,
                maxNodeSize: 10,
                drawLabels: false,

                enableEdgeHovering: false,
                edgeHoverColor: 'default',
                defaultEdgeHoverColor: "#eee",
                defaultHoverLabelBGColor: '#eee',
                customLabel: true,

                edgeColor: 'default',
                defaultEdgeColor: '#aaa',


                doubleClickEnabled: false,
                minEdgeSize: 1,
                maxEdgeSize: 1,
                edgeHoverSizeRatio: 1,
                edgeHoverExtremities: false
            }
        });

        sigma.parsers.json('data/<?php echo ApplicationConfig::lastFmUser ?>.json', s, function() {
            s.startForceAtlas2({
                linLogMode: true,
                strongGravityMode: true,
                adjustSizes: true,
                edgeWeightInfluence: 0
            });

            s.bind('clickNode', function() {
                if(s.isForceAtlas2Running()) {
                    s.stopForceAtlas2();
                }

                else {
                    s.startForceAtlas2();
                }

            });

            console.log(
                'Artistes : ' + s.graph.nodes().length + '\nConnexions : ' + s.graph.edges().length
            );
        });
    </script>
</body>
</html>