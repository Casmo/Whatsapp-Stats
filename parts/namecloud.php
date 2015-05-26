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
    $list = $Whatsapp->getNameList();
}
$listJson = '[';
$first = false;
foreach ($list as $word => $count) {
    if ($first) {
        $listJson .= ',';
    }
    $listJson .= "['". $word ."', ". $count ."]";
    $first = true;
}
$listJson .= ']';
?>
<canvas width="1024" height="768" id="canvas"></canvas>

<script>
    var canvas = document.getElementById('canvas');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight - 55;
    WordCloud(document.getElementById('canvas'), { list: <?php echo $listJson; ?> } );
</script>