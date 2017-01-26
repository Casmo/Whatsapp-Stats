<?php
/**
 * File index.php
 * @author Mathieu de Ruiter <www.fellicht.nl>
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__) . DS);
define('CHAT_FILE', ROOT . 'tmp' . DS . 'chat.log');

if (isset($_FILES['chat']['tmp_name']) && !empty($_FILES['chat']['tmp_name'])) {
    move_uploaded_file($_FILES['chat']['tmp_name'], CHAT_FILE);
}

$module = '';
if (isset($_GET['module'])) {
    $module = $_GET['module'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Generate stats from a group chat</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/default.css"/>
    <script src="js/wordcloud.js"></script>
    <script src="js/chart.min.js"></script>
    <script src="js/randomColor.js"></script>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <a href="http://www.fellicht.nl" target="_blank"
           class="pull-right navbar-brand">Fellicht.nl</a>
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">Home</a>
            <?php
            if (is_file(CHAT_FILE)) {
                ?>
                <ul class="nav navbar-nav">
                    <li<?php if ($module == 'wordcloud') {
                        echo ' class="active"';
                    } ?>><a href="?module=wordcloud">Wordcloud</a></li>
                    <li<?php if ($module == 'namecloud') {
                        echo ' class="active"';
                    } ?>><a href="?module=namecloud">Namecloud</a></li>
                    <li<?php if ($module == 'time') {
                        echo ' class="active"';
                    } ?>><a href="?module=time">Time</a></li>
                    <li<?php if ($module == 'relations') {
                        echo ' class="active"';
                    } ?>><a href="?module=relations">Relations</a></li>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>
</nav>
<?php
switch ($module) {
    case 'wordcloud':
        include 'parts/wordcloud.php';
        break;
    case 'namecloud':
        include 'parts/namecloud.php';
        break;
    case 'time':
        include 'parts/time.php';
        break;
    case 'relations':
        include 'parts/relations.php';
        break;
    default:
        include 'parts/home.php';
}
?>
</body>
</html>