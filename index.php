<html>
<head>
</head>
<?php

require_once "config.php";

session_start();

function execute($cmd) {
    exec($cmd);
    $_SESSION['success'] = "Command started, you should check the shell output<br>" . htmlentities($cmd);
}

if ( strlen($_POST['run_wrap_test'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh wraptest.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['tomcat_stop'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh stop.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['tomcat_start'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh start.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['tomcat_new'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh na.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['compile_sakai'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh qmv.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['database_new'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh db.sh > /dev/null &";
    execute($cmd);
    header("Location: index.php");
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
<li><a href="http://localhost:8080/" target="_new">Launch Sakai in a Browser</a></li>
<li><a href="tail.php?file=/tmp/shellout" target="_new">Tail Shell Output</a></li>
<li><input type="submit" name="run_wrap_test" value="Run Simple Shell Test"></li>
<li><input type="submit" name="tomcat_stop" value="Stop Tomcat"></li>
<li><input type="submit" name="tomcat_start" value="Start Tomcat">
<a href="tail.php?file=<?= $CFG['sakaihome'] ?>/apache-tomcat-9.0.21/logs/catalina.out" target="_new">Tail catalina.out</a></li>
<li><input type="submit" name="tomcat_new" value="Set up fresh Tomcat">
(Make sure to stop first)
</li>
<li><input type="submit" name="compile_sakai" value="Compile all of Sakai">
</li>
<li><input type="submit" name="database_new" value="Empty out the Database">
</li>
</ul>
</form>


</body>
</html>
