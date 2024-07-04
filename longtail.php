<html>
<head>
<?php
$file = $_GET['file'] ?? null;

if ( !file_exists($file) ) die("File not found");

?>
<style>
ol {
  font-family: monospace;
  white-space: pre;
}

li {
padding-bottom: 0;
}

li::marker {
  font-size: 10px;
  color: grey;
}
main {
  padding: 0;
}

header {
  position: sticky;
  top:0;
  padding:1em;
  background: lightblue;
  text-align: center;
}
</style>
</head>
<body>
<main>
  <header>
<form method="POST">
This file is too large to dynamicaly monitor so you will need to refresh the page to see updates.<br/>
<input type="submit" onclick=";window.location.reload(true)return false;" value="Reload page"/>
<input type="submit" onclick="document.getElementById('the_beginning').scrollIntoView(false);return false;" value="Goto top"/>
<input type="submit" onclick="document.getElementById('the_end').scrollIntoView(false);return false;" value="Goto bottom"/>
<?php if ( $file == "/tmp/shellout" ) { ?>
<input type="submit" name="reset_shell_out" value="Reset Output">
<?php } ?>
  File: <?= htmlentities($file) ?>
</form>
  </header>
  <content>
<span id="the_beginning"></span>
<pre>
<?php

$fp = fopen($file, 'r');
$lines = 0;

while (($line = fgets($fp)) !== false) {
    echo(trim(htmlentities($line))."\n");
    $lines++;
}
fclose($fp);

echo("\n");
echo("Lines: $lines \n");
?>
</pre>
<span id="the_end"></span>
</content>
</main>
<script>
document.getElementById('the_end').scrollIntoView(false);
</script>
</body>
</html>

