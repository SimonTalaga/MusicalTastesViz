/**
 * Created by Simon on 23/02/15.
 */
var s = null;

$(document).ready(function() {
    $('#graphForm').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var username = form.find('#username').val();
        console.log(username);
        $.ajax(
            {
                async: true,
                url: form.attr("action"),
                type: form.attr("method"),
                data: {user: username},
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
                                    form.find('progress').attr('value', data);
                                }
                            );
                        },
                        300
                    );
                },

                success: function(data)
                {
                    clearInterval(this.progressChecker);
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
                            defaultHoverLabelBGColor: '#eee',

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

                    sigma.parsers.json('data/'+ username +'.json', s, function() {
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
    });
});
