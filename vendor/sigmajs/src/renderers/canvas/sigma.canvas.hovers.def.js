;(function(undefined) {
  'use strict';

  if (typeof sigma === 'undefined')
    throw 'sigma is not declared';

  // Initialize packages:
  sigma.utils.pkg('sigma.canvas.hovers');

  /**
   * This hover renderer will basically display the label with a background.
   *
   * @param  {object}                   node     The node object.
   * @param  {CanvasRenderingContext2D} context  The canvas context.
   * @param  {configurable}             settings The settings function.
   */
  sigma.canvas.hovers.def = function(node, context, settings) {
    var x,
        y,
        w,
        h,
        e,
        fontStyle = settings('hoverFontStyle') || settings('fontStyle'),
        prefix = settings('prefix') || '',
        size = node[prefix + 'size'],
        fontSize = (settings('labelSize') === 'fixed') ?
          settings('defaultLabelSize') :
          settings('labelSizeRatio') * size;

    // Label background:
    context.font = (fontStyle ? fontStyle + ' ' : '') +
      fontSize + 'px ' + (settings('hoverFont') || settings('font'));

    context.beginPath();
    context.fillStyle = settings('labelHoverBGColor') === 'node' ?
      (node.color || settings('defaultNodeColor')) :
      settings('defaultHoverLabelBGColor');

    if (node.label && settings('labelHoverShadow')) {
      context.shadowOffsetX = 0;
      context.shadowOffsetY = 0;
      context.shadowBlur = 8;
      context.shadowColor = settings('labelHoverShadowColor');
    }

    /*
     var w = 150;
     var h = 90;

     var ox = 20;
     var oy = 20;

     var p = 12;

     context.beginPath();
     context.moveTo(ox, oy);
     context.lineTo(ox, h);
     context.lineTo(ox + (w / 2) - (p / 1.2), h);
     context.lineTo(ox + (w / 2), h + p);
     context.lineTo(ox + (w / 2) + (p / 1.2), h);
     context.lineTo(ox + w, h);
     context.lineTo(ox + w, oy);

     context.stroke();
     context.closePath();
     context.fill();
     */

     if (node.label && typeof node.label === 'string') {

        x = Math.round(node[prefix + 'x'] - fontSize / 2 - 2);
        y = Math.round(node[prefix + 'y'] - fontSize / 2 - 2);
        w = Math.round(
            context.measureText(node.label).width + fontSize / 2 + size + 7
        );
        h = Math.round(fontSize + 4);
        e = Math.round(fontSize / 2 + 2);

        if(settings('customLabel')) {
            h = 90;
            // On prend en compte la taille de l'image
            w = w + h;
            var ox = x - w / 2 + fontSize / 2;
            var p = 12;
            var oy;

            context.moveTo(ox, oy);

            if(y <= h + 5) {
                oy = y + size * 2 + p * 2;
                context.moveTo(ox, oy);
                context.lineTo(ox + (w / 2) - (p / 1.2), oy);
                context.lineTo(ox + (w / 2), oy - p);
                context.lineTo(ox + (w / 2) + (p / 1.2), oy);
                context.lineTo(ox + w, oy);
                context.lineTo(ox + w, oy + h);
                context.lineTo(ox, oy + h);
            }

            else {
                oy = y - h - size - 5 ;
                context.moveTo(ox, oy);
                context.lineTo(ox, oy + h);
                context.lineTo(ox + (w / 2) - (p / 1.2), oy + h);
                context.lineTo(ox + (w / 2), oy + h + p);
                context.lineTo(ox + (w / 2) + (p / 1.2), oy + h);
                context.lineTo(ox + w, oy + h);
                context.lineTo(ox + w, oy);
            }

            context.stroke();
            context.closePath();
            context.fill();

            // Disable shadows
            context.shadowOffsetX = 0;
            context.shadowOffsetY = 0;
            context.shadowBlur = 0;

            // Display the thumbnail
            var img = document.createElement('img');
            img.src = node.img;
            img.onload = function () {
                context.drawImage(img, (126 - h) / 2, 0, 126, 84, ox, oy, 126 - ((126 - h) / 2), h);
            };

            // Display the label:
            context.fillStyle = (settings('labelHoverColor') === 'node') ?
                (node.color || settings('defaultNodeColor')) :
                settings('defaultLabelHoverColor');

            context.fillText(
                node.label,
                ox + h + fontSize / 2,
                oy + fontSize + 2
            );
        }

        else {
            context.moveTo(x, y + e);
            context.arcTo(x, y, x + e, y, e);
            context.lineTo(x + w, y);
            context.lineTo(x + w, y + h);
            context.lineTo(x + e, y + h);
            context.arcTo(x, y + h, x, y + h - e, e);
            context.lineTo(x, y + e);

            context.closePath();
            context.fill();

            // Disable shadows
            context.shadowOffsetX = 0;
            context.shadowOffsetY = 0;
            context.shadowBlur = 0;

            // Display the label:
            context.fillStyle = (settings('labelHoverColor') === 'node') ?
                (node.color || settings('defaultNodeColor')) :
                settings('defaultLabelHoverColor');

            context.fillText(
                node.label,
                Math.round(node[prefix + 'x'] + size + 3),
                Math.round(node[prefix + 'y'] + fontSize / 3)
            );
        }
     }

    // Node border:
    if (settings('borderSize') > 0) {
      context.beginPath();
      context.fillStyle = settings('nodeBorderColor') === 'node' ?
        (node.color || settings('defaultNodeColor')) :
        settings('defaultNodeBorderColor');
      context.arc(
        node[prefix + 'x'],
        node[prefix + 'y'],
        size + settings('borderSize'),
        0,
        Math.PI * 2,
        true
      );
      context.closePath();
      context.fill();
    }

    // Node:
    var nodeRenderer = sigma.canvas.nodes[node.type] || sigma.canvas.nodes.def;
    nodeRenderer(node, context, settings);

  };
}).call(this);
