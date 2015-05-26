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
    $list = $Whatsapp->getTimeList();
}
?>
<canvas width="1024" height="768" id="canvas"></canvas>

<script>
    var canvas = document.getElementById('canvas');
    var ctx = canvas.getContext("2d");

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight - 55;

    var hours = [];
    for (var i = 0; i < 24; i++) {
        var hour = i;
        if (hour < 10) {
            hour = '0' + hour;
        }
        hour = hour + ':00';
        hours.push(hour);
    }
    var hourData = [];
    <?php
foreach ($list as $hour => $count) {
    ?>
    hourData.push(<?php echo $count; ?>);
    <?php
}
 ?>
    var data = {
        labels: hours,
        datasets: [
            {
                label: "Messages per hour",
                fillColor: "rgba(255,100,0,0.6)",
                strokeColor: "rgba(255,100,0,1)",
                pointColor: "rgba(255,100,0,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: hourData
            }
        ]
    };

    var myLineChart = new Chart(ctx).Line(data);

</script>