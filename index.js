/**
 * Created by Simon on 23/02/15.
 */
var s = null;

$(document).ready(function() {

    $('.display-data').click(function() {
       var username = $(this).parent().find('.content span').text();
       fetchData(username);
    });

    $('#graphForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var username = form.find('#username').val();
        $('#progressBar').show();
        fetchData(username);
    });

    function fetchData(username) {
        var form = $('#graphForm');
            $.ajax(
            {
                async: true,
                url: form.attr("action"),
                type: form.attr("method"),
                data: {action: 'buildGraph', user: username},
                progressChecker: null,
                beforeSend: function()
                {
                    this.progressChecker = setInterval(
                        function()
                        {
                            $.post(
                                "checkProgress.php",
                                function(data)
                                {
                                    data = JSON.parse(data);
                                    form.find('#progressBar .label').text(data.step + " " + data.progress + " / " + data.total);
                                    var percent = Math.ceil(parseInt(data.progress) / parseInt(data.total) * 100);
                                    form.find('#progressBar .bar').css('width', percent + '%');
                                }
                            );
                        },
                        300
                    );
                },

                success: function(data)
                {
                    clearInterval(this.progressChecker);

                    $.ajax(
                        {
                            url: 'router.php',
                            data: {action: 'analyzeTracks', user: username},
                            progressChecker: null,
                            beforeSend: function()
                            {
                                $('.tastes-content').hide();
                                $('.tastes .loader').addClass('active');
                                this.progressChecker = setInterval(
                                    function()
                                    {
                                        $.post(
                                            "checkProgress.php",
                                            function(data)
                                            {
                                                data = JSON.parse(data);
                                                form.find('#progressBar .label').text(data.step + " " + data.progress + " / " + data.total);
                                                var percent = Math.ceil(parseInt(data.progress) / parseInt(data.total) * 100);
                                                form.find('#progressBar .bar').css('width', percent + '%');
                                            }
                                        );
                                    },
                                    300
                                );
                            },
                            success: function(data) {
                                clearInterval(this.progressChecker);
                                $('#progressBar').hide();

                                $.get('router.php', {action: 'getFavoritePitch', user: username}, function(data) {
                                    data = JSON.parse(data);
                                    $('#pitch blockquote').text(data.desc);
                                    $('#pitch .key').text(data.key);
                                    $('#pitch .mode').text(data.mode);
                                });

                                $.get('router.php', {action: 'getAcousticTastes', user: username}, function(data) {
                                    data = JSON.parse(data);
                                    var ctx = document.getElementById('diamond').getContext('2d');
                                    var dataset = {
                                        labels: ["Danceability", "Energy", "Speechiness", "Acousticness", "Valence"],
                                        datasets: [{
                                            label: "My First dataset",
                                            fillColor: "rgba(220,220,220,0.2)",
                                            strokeColor: "rgba(220,220,220,1)",
                                            pointColor: "rgba(220,220,220,1)",
                                            pointStrokeColor: "#fff",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(220,220,220,1)",
                                            data: [Math.ceil(parseFloat(data.danceability) * 100), Math.ceil(parseFloat(data.energy) * 100), Math.ceil(parseFloat(data.speechiness) * 100), Math.ceil(parseFloat(data.acousticness) * 100), Math.ceil(parseFloat(data.valence) * 100)]
                                        }]
                                    };
//
                                    var myRadarChart = new Chart(ctx).Radar(dataset, {
                                        scaleOverride   : true,
                                        responsive: true,
                                        scaleFontSize: 30,
                                        pointLabelFontSize : 15,
                                        scaleSteps      : 5,
                                        scaleStepWidth  : 20,
                                        scaleStartValue : 0,
                                        pointDotRadius : 5

                                    });
                                });

                                $('.tastes-content').show();
                                $('.tastes .loader').removeClass('active');
                            }
                        }
                    );


                    if(s instanceof sigma) {
                        s.kill();
                    }

                    s = new sigma({
                        container: 'graph-container',
                        renderer: {
                            container: document.getElementById('graph'),
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
                            defaultHoverLabelBGColor: '#fff',

                            // Custom params
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

                    sigma.parsers.json('data/'+ username +'/artists-graph.json', s, function() {
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
                }
            }
        );
    }
});
