<html>
<head>
</head>
<?php

require_once "config.php";
require_once "relativeTime.php";

date_default_timezone_set('America/Detroit');

session_start();

$note = "No note so far.";
$lastupdate =  false;
if ( file_exists("note.txt") ) {
    $lastupdate = date ("F d Y H:i:s.", filemtime("note.txt"))." (Eastern)";
    $lastupdate = relativeTime(filemtime("note.txt"));
    $note = file_get_contents("note.txt");
}

function execute($cmd) {
    exec($cmd);
    $_SESSION['success'] = "Command started, you should check the shell output<br>" . htmlentities($cmd);
}

if ( strlen($_POST['run_wrap_test'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh 'wraptest.sh parm1' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_status'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git status' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_branches'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git branch -a' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_remotes'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git remote -v' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_branch'] ?? '') > 0 && strlen($_POST['branch_name'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git fetch origin ".$_POST['branch_name'].":".$_POST['branch_name']."; git checkout ".$_POST['branch_name']."' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_repo'] ?? '') > 0 && strlen($_POST['repo_name'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'bash co.sh ".$_POST['repo_name']."' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['note'] ?? '') > 0 ) {
    file_put_contents("note.txt", $_POST['note']);
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

if ( strlen($_POST['new_property'] ?? '') > 0 && strlen($_POST['change_property'] ?? '') > 0) {
    $new_prop = trim($_POST['new_property']);
    $pos = strpos($new_prop, '=');

    if ( ! ($pos >= 1) ) {
        $_SESSION['error'] = "property=value required.";
        header("Location: index.php");
        return;
    }

    $propfile = $CFG['sakaihome'] . '/apache-tomcat-9.0.21/sakai/sakai.properties';
    if ( ! file_exists($propfile) ) {
        $_SESSION['error'] = "Could not load: ".$propfile;
        header("Location: index.php");
        return;
    }

    $props = file_get_contents($propfile);
    echo("<pre>\n");echo($props);echo("\n</pre>\n");

    $key = substr($new_prop, 0, $pos);

    $pos = strpos($props, $key."=");
    die("key $key Pos $pos");
    header("Location: index.php");
    return;
}


echo("<body>\n");

if ( strlen($_SESSION['error'] ?? '' ) > 0 ) {
    echo('<p style="color:red;">');
    echo($_SESSION['error']);
    echo("</p>\n");
    unset($_SESSION['error']);
}

if ( strlen($_SESSION['success'] ?? '' ) > 0 ) {
    echo('<p style="color:green;">');
    echo($_SESSION['success']);
    echo("</p>\n");
    unset($_SESSION['success']);
}

?>

<h1>Sakai Test Harness</h1>
<form method="POST">
<textarea name="note" style="width:70%;">
<?php echo(htmlentities($note)); ?>
</textarea>
<p>Enter a note above to let folks know when you are using this system and when you will be done.
<input type="submit" name="new_note" value="Update note">
<?php echo($lastupdate); ?>
</p>
</form>
<form method="POST">
<ul>
<hr/>
<li><input type="submit" name="git_status" value="Git Status in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="git_branches" value="Show Branches in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="git_remotes" value="Show Remotes in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<input type="text" style="width:25%;" name="repo_name">
<input type="submit" name="git_repo" value="Checkout repository">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<input type="text" style="width:25%;" name="branch_name">
<input type="submit" name="git_branch" value="Checkout branch">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<hr/>
<li>
<a href="tail.php?file=<?= $CFG['sakaihome'] ?>/apache-tomcat-9.0.21/sakai/sakai.properties" target="properties">View sakai.properties</a>
<br/>
<input type="submit" name="change_property" value="Add/Update a sakai.property">
<input type="text" style="width:50%" name="new_property">
</li>
<li><input type="submit" name="compile_sakai" value="Compile all of Sakai">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<hr/>
<li><input type="submit" name="tomcat_start" value="Start Tomcat">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a> |
<a href="tail.php?file=<?= $CFG['sakaihome'] ?>/apache-tomcat-9.0.21/logs/catalina.out" target="catalina">Tail catalina.out</a></li>
<li><a href="http://localhost:8080/" target="_new">Launch Sakai in a Browser</a>
</li>
<li><input type="submit" name="tomcat_stop" value="Stop Tomcat">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<hr/>
<li><input type="submit" name="tomcat_new" value="Set up fresh Tomcat">
(Make sure to stop first)
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="database_new" value="Reset the Database">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<hr/>
<li><input type="submit" name="run_wrap_test" value="Run Simple Shell Test">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
</ul>
</form>


</body>
</html>
