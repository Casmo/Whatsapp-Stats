<?php
/**
 * File wordcloud.php
 *
 * @author Mathieu de Ruiter <www.fellicht.nl>
 */
if (!defined('ROOT')) {
    die('Invalid request.');
}
require ROOT . 'classes' . DS . 'whatsapp.php';
$Whatsapp = new Whatsapp();
$list = array();
if ($Whatsapp->readFile(CHAT_FILE)) {
    $list = $Whatsapp->getRelations();
}

$heightest = 0;
foreach ($list as $name => $friends) {
    $total = 0;
    foreach ($friends as $amount) {
        $total += $amount;
        if ($total > $heightest) {
            $heightest = $total;
        }
    }
}

?>
<canvas width="1024" height="768" id="canvas"></canvas>

<script>
    var heightest = <?php echo $heightest; ?>;
    var maxFontSize = 60;
    var maxLineSize = 1.5;
    var names = [];
    var connectionsMade = {};
    var canvas = document.getElementById('canvas');
    canvas.width = (window.innerWidth * 1.5);
    canvas.height = (window.innerWidth * 1.5); // Circle
    var ctx = canvas.getContext('2d');
    ctx.font = "18px 'Open Sans', Arial";
    ctx.fillStyle = "#000000";
    ctx.strokeStyle = "rgba(0,0,0,.2)";
    ctx.textBaseline = "middle";
    var centerX = canvas.width / 2;
    var centerY = canvas.height / 2;
    var radius = (canvas.height / 2) - 350;
    var steps = <?php echo count($list); ?>;
    var stepI = 0;
    <?php
    foreach ($list as $name => $relations) {
    ?>
    names.push({
        name: '<?php echo $name; ?>',
        relations: <?php echo json_encode($relations); ?>,
        x: (centerX + radius * Math.cos(2 * Math.PI * stepI / steps)), // Math.floor(Math.random() * (canvas.width - 100)),
        y: (centerY + radius * Math.sin(2 * Math.PI * stepI / steps)), //Math.floor(Math.random() * (canvas.height - 50)),
        color: randomColor({
            luminosity: 'dark',
            format: 'rgba',
            alpha: 1
        }),
        width: 0, // text width
        height: 0 // text height
    });
    stepI++;
    <?php
    }
    ?>
    function init() {
        for (var i = 0; i < names.length; i++) {
            var name = names[i];
            var total = 0;
            var max = 0;
            for (var friend in name.relations) {
                if (name.relations.hasOwnProperty(friend)) {
                    total += name.relations[friend];
                    if (max < name.relations[friend]) {
                        max = name.relations[friend];
                    }
                }
            }
            var size = 1 / heightest * total;
            var fontsize = maxFontSize * size;
            if (fontsize < 16) {
                fontsize = 16;
            }
            ctx.font = fontsize + "px 'Open Sans', Arial";
            var text = ctx.measureText(name.name);
            names[i].width = text.width;
            names[i].height = fontsize;
            ctx.fillStyle = name.color;
            ctx.fillText(name.name, name.x, name.y);
            for (var friendName in name.relations) {
                if (name.relations.hasOwnProperty(friendName)) {
                    for (var j = 0; j < names.length; j++) {
                        if (friendName == names[j].name) {
                            var out = true;
                            if (connectionsMade[name.name] == null) {
                                connectionsMade[name.name] = friendName;
                                out = false;
                            }
                            var amount = name.relations[friendName];
                            var friendX = names[j].x;
                            var friendY = names[j].y;
                            var size = 1 / max * amount;
                                if (size < .3) {
                                    size = .3;
                                }

                            var style = name.color.replace(", 1)", ", "+ size +")");
                            ctx.strokeStyle = style;
                            ctx.fillStyle = style;
                            var lineWith = maxLineSize * size;
                            ctx.beginPath();
                            var x = name.x + name.width;
                            var y = name.y;
                            ctx.moveTo(x, y);
                            ctx.lineWidth = lineWith;
                            var cpX = x + 150;
                            var cpY = y + 150;
                            var cpX2 = friendX - 150;
                            var cpY2 = friendY + 150;
                            if (y > friendY) {
                                cpY = y - 150;
                                cpY2 = y - 150;
                            }
                            var t = 0.5; // given example value
                            var fontX = (1 - t) * (1 - t) * x + 2 * (1 - t) * t * cpX + t * t * cpX2;
                            var fontY = (1 - t) * (1 - t) * y + 2 * (1 - t) * t * cpY + t * t * cpY2;
                            fontX += 5;
                            fontY += 5;
                            ctx.font = "14px 'Open Sans', Arial";
                            ctx.fillText(amount, fontX, fontY);
                            ctx.bezierCurveTo(cpX, cpY, cpX2, cpY2, friendX, friendY);
                            ctx.stroke();
                        }
                    }
                }
            }
        }
    }
    window.onload = init;
</script>