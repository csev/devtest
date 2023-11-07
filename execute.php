<?php

if ( file_exists("config.php") ) {
require_once "config.php";
} else {
require_once "config-dist.php";
}

require_once "relativeTime.php";

$secret = $CFG['unlock'] ?? '42';

if ( isset($_POST['secret']) && ($_POST['secret'] == $secret ) ) {
    setCookie('secret', $secret, time() + 15 * 3600 * 24);
    header("Location: execute.php");
    return;
} else if ( !isset($_COOKIE['secret']) || $_COOKIE['secret'] != $secret ) {
?>
<body style="font-family: Courier,monospace; width: 80%; max-width:500px;margin-left: auto; margin-right: auto;">
<center>
<h1>Sakai QA Dev Test Unlock</h1>
<form method="post">
<input type="text" name="secret">
<input type="submit" value="Unlock">
<p>
<?php
    return;
}
?><html>
<head>
</head>
<?php

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
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_status'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git status' > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_log'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git log -20 ' > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_branches'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git branch -a' > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_remotes'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git remote -v' > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_branch'] ?? '') > 0 && strlen($_POST['branch_name'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git fetch origin ".$_POST['branch_name'].":".$_POST['branch_name']."; git checkout ".$_POST['branch_name']."' > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['git_repo'] ?? '') > 0 && strlen($_POST['repo_name'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'bash co.sh ".$_POST['repo_name']."' > /dev/null &";
    $_SESSION['repo_name'] = $_POST['repo_name'];
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['note'] ?? '') > 0 ) {
    file_put_contents("note.txt", $_POST['note']);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['tomcat_stop'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh stop.sh > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['tomcat_start'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh start.sh > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['tomcat_new'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh na.sh > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['compile_sakai'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh qmv.sh > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['database_new'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']." ; nohup ./wrap.sh db.sh > /dev/null &";
    execute($cmd);
    header("Location: execute.php");
    return;
}

if ( strlen($_POST['new_property'] ?? '') > 0 && strlen($_POST['change_property'] ?? '') > 0) {
    $new_prop = trim($_POST['new_property']);
    $pos = strpos($new_prop, '=');

    if ( ! ($pos >= 1) ) {
        $_SESSION['error'] = "property=value required.";
        header("Location: execute.php");
        return;
    }

    $propfile = $CFG['sakaihome'] . '/apache-tomcat-9.0.21/sakai/sakai.properties';
    if ( ! file_exists($propfile) ) {
        $_SESSION['error'] = "Could not load: ".$propfile;
        header("Location: execute.php");
        return;
    }

    $props = file_get_contents($propfile);
    // echo("<pre>\n");echo($props);echo("\n</pre>\n");

    $key = substr($new_prop, 0, $pos);

    // Find the start of the property
    $pos = strpos($props, $key."=");
    if ( $pos !== false ) {
        $pos2 = strpos($props, "\n", $pos);
        if ( $pos2 === false ) {
            $props = substr($props, 0, $pos) . $new_prop . "\n";
            $_SESSION['success'] = "Property updated at the end of the file";
        } else {
            $props = substr($props, 0, $pos) . $new_prop . substr($props, $pos2);
            $_SESSION['success'] = "Property updated";
        }
    } else {
       $_SESSION['success'] = "Property added at the end of the file";
        $props = $props . "\n" . $new_prop . "\n";
    }
    // echo("<pre>\n");echo($props);echo("\n</pre>\n");
    file_put_contents($propfile, $props);

    header("Location: execute.php");
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

$repo_name = "https://github.com/sakaiproject/sakai";
if ( isset($_SESSION["repo_name"]) ) $repo_name = $_SESSION["repo_name"];
?>

<h1>Compile / Execute</h1>
<p>
You use this page to configure Sakai, compile Sakai, start Sakai and then test your instance of Sakai.
If you want to switch to a different version of Sakai go back to the check out page and
pull down a new version.
</p>
<p>
<button onclick="window.location.href='index.php';return false;">Go back to Checkout / Setup </button>
</p>
<hr/>
<form method="POST">
<ul>
<li>
<button onclick="window.open('code/','_blank');return false;">View Sakai Source Code</button>
</li>
<li>
<button onclick="window.open('<?= $CFG["phpMyAdmin"] ?>','_blank');return false;">View the Database</button>
</li>
<hr/>
<li>
<input type="submit" name="compile_sakai" value="Compile all of Sakai">
(7+ minutes)
<a href="tail.php?file=/tmp/shellout&large=true" target="shell">Tail shell output</a>
</li>
<hr/>
<li>
<a href="tail.php?file=<?= $CFG['sakaihome'] ?>/apache-tomcat-9.0.21/sakai/sakai.properties" target="properties">View sakai.properties</a>
<br/>
<input type="submit" name="change_property" value="Add/Update a sakai.property">
<input type="text" style="width:50%" name="new_property">
<p>
Simply enter a line to be added to the file with the property name and its value.
</p>
</li>
<hr/>
<li><input type="submit" name="tomcat_start" value="Start Tomcat">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a> |
<a href="tail.php?file=<?= $CFG['sakaihome'] ?>/apache-tomcat-9.0.21/logs/catalina.out&large=true" target="catalina">Tail catalina.out</a></li>
<button onclick="window.open('<?= $CFG["sakaiserver"] ?>','_blank');return false;">Launch Sakai in a Browser</button>
</li>
<li><input type="submit" name="tomcat_stop" value="Stop Tomcat">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
</ul>
</form>
<p>
The source code for this is available at:
<ul>
<li><a href="https://github.com/csev/devtest" target="_new">https://github.com/csev/devtest</a></li>
<li><a href="https://github.com/csev/sakai-scripts" target="_new">https://github.com/csev/sakai-scripts</a></li>
</ul>
</body>
</html>
