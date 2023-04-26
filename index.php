<html>
<head>
</head>
<?php

require_once "config.php";

session_start();

function execute($cmd) {
    exec($cmd);
    $_SESSION['success'] = "Simple test started, you should check the shell output<br>" . htmlentities($cmd);
    header("Location: index.php");
}

if ( strlen($_POST['run_wrap_test'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh wraptest.sh > /dev/null &";
    execute($cmd);
    return;
}

echo("<body>\n");

if ( strlen($_SESSION['success'] ?? '' ) > 0 ) {
    echo('<p style="color:green;">');
    echo($_SESSION['success']);
    echo("</p>\n");
    unset($_SESSION['success']);
}

?>

<form method="POST">
<ul>
<li><a href="tail.php?file=/tmp/shellout" target="_new">Tail Shell Output</a></li>
<li><input type="submit" name="run_wrap_test" value="Run Simple Shell Test"></li>
</ul>
</form>


</body>
</html>
