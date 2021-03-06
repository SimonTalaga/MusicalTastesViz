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
    <script src="vendor/jquery/jquery.min.js"></script>

    <style>
        #graph {
            width: 800px;
            height: 600px;
            background-color: #fff;
        }
    </style>
</head>

<body>
    <form id="graphForm" action="buildGraph.php" method="POST">
        <label for="username">Lastfm User :</label>
        <input type="text" id="username" />
        <button id="buildGraph">Générer le graphe</button>
        <progress value="0" max="100">Pchitt</progress>
    </form>

    <br/>

    <div id="graph-container">
        <div id="graph"></div>
    </div>

    <script src="index.js"></script>
</body>
</html>