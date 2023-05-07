<?php
require_once "../config.php";

$secret = $CFG['unlock'] ?? '42';

if ( ($_COOKIE['secret'] ?? '') != $secret ) {
    header("Location: ../index.php");
    return;
}

// http://localhost:8888/devtest/code/plus/
// http://localhost:8888/devtest/code/plus/pom.xml
$url = $_SERVER['REQUEST_URI'];
$pieces = array_filter(explode('/',$url));

$parent = array();
while(is_string($piece = array_shift($pieces)) ) {
    array_push($parent, $piece);
    if ( $piece == 'code' ) break;
}
$parent_url = $path = implode('/', $parent);

foreach($pieces as $piece) {
    if ( strpos($piece, '.') === 0 ||
         strpos($piece, '~') != false ||
         strpos($piece, '/') != false ) {
        die('Bad path');
    }
}

// plus
// plus/pom.xml
$path = implode('/', $pieces);
$path = $CFG['sakaihome'] . '/trunk/' . implode('/', $pieces);

if ( is_dir($path) ) {
    $is_folder = true;
    $contents = scandir($path);
} else if ( file_exists($path) ) {
    $is_folder = false;
    $code = file_get_contents($path);
} else {
    die("Path $path not found");
}

// TODO: Handle POST here

$lines = 42;
?>
<!doctype html>
<html>
<head>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css" integrity="sha512-uf06llspW44/LZpHzHT6qBOIVODjWtv4MxCricRxkzvopAlSWnTf6hpZTFxuuZcuNE9CBQhqE0Seu1CoRk84nQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

<style>
body {
    font-family: Courier, monospace;
}

.CodeMirror { height: auto; border: 1px solid #ddd; }
/* .CodeMirror-scroll { max-height: <?= intval(($lines/13)*20) ?>em; } */

.pre_text {
    height: auto;
    max-height: 200px;
    overflow: auto;
    /*overflow-y: none;*/
    background-color: #eeeeee;
}

.CodeMirror-scroll { max-height: <?= intval(($lines/13)*20) ?>em; }
</style>
</head>
<body>
Path: <?php
$piece_url = '/' .$parent_url ;
$piece_path = $CFG['sakaihome'] . '/trunk';

echo('<a href="/');
echo(htmlentities($parent_url));
echo('/">Sakai</a>');

// TODO: Look at pom.xml files along the way to find possible compile points
foreach($pieces as $piece) {
    $piece_url = $piece_url . '/' . $piece;
    $piece_path = $piece_path . '/' . $piece;
    echo('/<a href="');
    echo(htmlentities($piece_url));
    if ( is_dir($piece_path) ) { echo('/'); }
    echo('">');
    echo(htmlentities($piece));
    echo("</a>");
}

?>
<?php if ( $is_folder ) { ?>
<ul>
<?php
$folders = array();
$files = array();
foreach($contents as $entry ) {
    if ( !is_string($entry) ) continue;
    if ( strpos($entry, '.') === 0 ) continue;
    $sub_path = $path . '/' . $entry;
    if ( is_dir($sub_path) ) {
        array_push($folders, $entry);
    } else {
        array_push($files, $entry);
    }
}

foreach($folders as $folder) {
    echo('<li><i class="bi bi-folder"></i> ');
    echo('<a href="');
    echo(htmlentities($folder));
    echo('/">');
    echo(htmlentities($folder));
    echo("</a></li>\n");
}
foreach($files as $file) {
    echo('<li><i class="bi bi-archive"></i> ');
    echo('<a href="');
    echo(htmlentities($file));
    echo('">');
    echo(htmlentities($file));
    echo("</a></li>\n");
}
?>
</ul>
<?php } else { // file... ?>
<p>
<textarea id="mycode" name="code" style="height: auto;">
<?php
if ( is_string($code) ) {
    echo(htmlentities($code));
} else {
?>
#include <stdio.h>

main() {
  printf("Hello World\n");
}
<?php } ?>
</textarea>
<?php 
} // End of folder or file
?>
<?php if ( ! $is_folder ) { ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js" integrity="sha512-8RnEqURPUc5aqFEN04aQEiPlSAdE0jlFS/9iGgUyNtwFnSKCXhmB6ZTNl7LnDtDWKabJIASzXrzD0K+LYexU9g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  var myTextarea = document.getElementById("mycode");
  var editor = CodeMirror.fromTextArea(myTextarea, {
        lineNumbers: true,
        matchBrackets: true,
        mode: "text/x-csrc"
  });
</script>
<?php } ?>

</body>
</html>
