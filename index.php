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
    header("Location: index.php");
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
    
    <!-- Simple CSS automatically enhances HTML with a cleaner, more aesthetically pleasing look without any additional styling needed -->
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    
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
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_status'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git status' > /dev/null &";
    execute($cmd);
    header("Location: index.php");
    return;
}

if ( strlen($_POST['git_log'] ?? '') > 0 ) {
    $cmd = "cd ".$CFG['sakaihome']."; nohup ./wrap.sh 'cd trunk; pwd; git log -20 ' > /dev/null &";
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
    $_SESSION['repo_name'] = $_POST['repo_name'];
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

$repo_name = "https://github.com/sakaiproject/sakai";
if ( isset($_SESSION["repo_name"]) ) $repo_name = $_SESSION["repo_name"];
?>

<h1>Sakai Checkout / Set Up</h1>
<form method="POST">
<p>
Use this page to check out the correct version of Sakai. Once you finish the setup on this page you can switch to the execution page.
</p>
<p>
You can watch a
<a href="https://youtu.be/_LDb57P5IQ8" target="_blank">demonstration video</a> to get a sense of how you can use this site.
</p>
<p>
Last note update:
<?php echo($lastupdate); ?>
<br/>
<textarea name="note" style="width:70%;">
<?php echo(htmlentities($note)); ?>
</textarea>
<br/>
Enter a note above to let folks know when you are using this system and when you will be done.
<input type="submit" name="new_note" value="Update note">
</p>
</form>
<hr/>
<form method="POST">
<ul>
<li><input type="submit" name="tomcat_stop" value="Stop Tomcat">
(< 1 minute)
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<input type="submit" name="tomcat_new" value="Set up fresh Tomcat">
This will reset any properties you have added.
(< 1 minute)
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="database_new" value="(optional) Reset the Database">
(< 1 minute)
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<button onclick="window.open('<?= $CFG["phpMyAdmin"] ?>','_blank');return false;">View the Database</button>
</li>
<hr/>
<li>
<input type="text" style="width:25%;" name="repo_name"  value="<?= htmlentities($repo_name) ?>">
<input type="submit" name="git_repo" value="Checkout repository"> (2 minutes)
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<button onclick="window.open('code/','_blank');return false;">View Sakai Source Code</button>
</li>
<li><input type="submit" name="git_status" value="Git Status in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="git_log" value="Git Log in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<hr/>
<li><input type="submit" name="git_remotes" value="Show Remotes in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li><input type="submit" name="git_branches" value="Show Branches in Sakai Folder">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<li>
<input type="text" style="width:25%;" name="branch_name">
<input type="submit" name="git_branch" value="Checkout branch">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
<!--
<hr/>
<li><input type="submit" name="run_wrap_test" value="Run Simple Shell Test">
<a href="tail.php?file=/tmp/shellout" target="shell">Tail shell output</a>
</li>
-->
</ul>
</form>
<hr/>
<p>
<button onclick="window.location.href='execute.php';return false;">Switch to Compile / Execute </button>
</p>
<p>
The source code for this is available at:
<ul>
<li><a href="https://github.com/csev/devtest" target="_new">https://github.com/csev/devtest</a></li>
<li><a href="https://github.com/csev/sakai-scripts" target="_new">https://github.com/csev/sakai-scripts</a></li>
</ul>
</body>
</html>
