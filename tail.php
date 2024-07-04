<html>
<head>
<?php
$start = $_GET['start'] ?? 1;
if ( $start < 1 ) $start = 1;
$limit = $_GET['limit'] ?? 0;
$file = $_GET['file'] ?? null;
$large = ($_GET['large'] ?? null ) !== null;

if ( !file_exists($file) ) die("File not found");

if ( $file == "/tmp/shellout" && strlen($_POST['reset_shell_out'] ?? '') > 0 ) {
    exec("rm /tmp/shellout");
    exec("touch /tmp/shellout");
    $_SESSION['success'] = "Shell output reset, make sure to reset your tail of shell output";
    header("Location: tail.php?file=".$file);
    return;
}

// This just craps out once a file gets too big potentially hanging Apache
// so check line count first
$fp = fopen($file, 'r');
$lines = 0;
while (($line = fgets($fp)) !== false) $lines++;
fclose($fp);

if ( $lines > 2000 ) {
    header("Location: longtail.php?file=".$file);
    return;
}

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
<input type="text" id="search" onkeydown="if (event.key == 'Enter') doSearch();"}>
<button onclick="doSearch(); return false;">Search</button>
<button onclick="document.getElementById('search').value=''; doSearch(); return false;">Clear</button>
Auto Scroll: <input type="checkbox" checked id="scroll">
<form method="POST">
<?php if ( $file == "/tmp/shellout" ) { ?>
<input type="submit" name="reset_shell_out" value="Reset Output">
<?php } ?>
  File: <?= htmlentities($file) ?>

</form>
  </header>
  <content>
<?php if ( $large ) { ?>
<pre id="tail">
<?php } else { ?>
<ol id="tail" start="<?= $start ?>">
<?php }

$fp = fopen($file, 'r');
$pos = 1;
$shown = 0;

while (($line = fgets($fp)) !== false) {
    if ( $pos >= $start ) {
        if ( ! $large ) { echo('<li style="background-color: white;">'); }
        echo(trim($line));
        // https://css-tricks.com/fighting-the-space-between-inline-block-elements/
        if ( ! $large ) { echo("<br/></li\n>"); } else { echo("\n"); }
        $shown++;
    }
    $pos++;
    if ( $limit > 0 && $shown >= $limit ) break;

}

if ( $large ) { echo("\n</pre>\n"); } else { echo("\n</ol>\n"); }
?>
<span id="the_end"></span>
</content>
</main>
<script>
// stdbuf -i0 -o0 -e0 bash yada.sh | tee -a /tmp/zap ; echo "Fini" >> /tmp/zap
var pos = <?= $pos ?>;
function doTail() {
    fetch('read.php?file=<?= $file ?>&start='+pos+'&limit='+<?= $limit ?>)
    .then(response => response.json())
    .then(data => {
      Object.keys(data.lines).forEach(function(key) {
          const scroll = document.getElementById('scroll').checked;
<?php if ( $large ) { ?>
          document.getElementById("tail").append(data.lines[key]);
          if ( scroll ) document.getElementById("the_end").scrollIntoView(false);
<?php } else { ?>
          var temp = document.createElement('li');
          temp.innerText = data.lines[key];

          const str = document.getElementById('search').value;
          if ( str.length > 0 && temp.innerText.toLowerCase().includes(str.toLowerCase()) ) {
              temp.style.backgroundColor = 'green';
          } else {
              temp.style.backgroundColor = 'white';
          }
          document.getElementById("tail").appendChild(temp);
          if ( scroll ) temp.scrollIntoView(false);
<?php } ?>
      })
      pos = data.next;
      setTimeout(doTail, 5000);
    });
}
setTimeout(doTail, 500);


function doSearch() {
    const str = document.getElementById('search').value;
    const parent = document.getElementById('tail');
    Array.from(parent.children).forEach((child, index) => {
        if ( str.length > 0 && child.innerText.toLowerCase().includes(str.toLowerCase()) ) {
            child.style.backgroundColor = 'green';
            child.scrollIntoView(false);
        } else {
            child.style.backgroundColor = 'white';
        }
    });
}
document.getElementById('search').value='';
document.getElementById('the_end').scrollIntoView(false);
</script>
</body>
</html>

