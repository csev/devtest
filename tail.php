<html>
<head>
<?php
$start = $_GET['start'] ?? 1;
if ( $start < 1 ) $start = 1;
$limit = $_GET['limit'] ?? 0;
$file = $_GET['file'] ?? null;

if ( !file_exists($file) ) die("File not found");
?>
<style>
ol {
  font-family: monospace;
  white-space: pre;
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

content > div {
  height: 50px;
}
</style>
</head>
<body>
<main>
  <header>
  File: <?= htmlentities($file) ?>

<input type="text" id="search" onkeydown="if (event.key == 'Enter') doSearch();"}>
<button onclick="doSearch(); return false;">Search</button>
<button onclick="document.getElementById('search').value=''; doSearch(); return false;">Clear</button>
Scroll: <input type="checkbox" checked id="scroll">
  </header>
  <content>
  <ol id="tail" start="<?= $start ?>">
</ol>
</content>
</main>
<script>
// stdbuf -i0 -o0 -e0 bash yada.sh | tee -a /tmp/zap ; echo "Fini" >> /tmp/zap
var pos = <?= $start ?>;
function doTail() {
    fetch('read.php?file=<?= $file ?>&start='+pos+'&limit='+<?= $limit ?>)
    .then(response => response.json())
    .then(data => {
      console.log(data);
      Object.keys(data.lines).forEach(function(key) {
          console.log('Key : ' + key + ', Value : ' + data.lines[key])
          var temp = document.createElement('li');
          temp.innerText = data.lines[key];

          const scroll = document.getElementById('scroll').checked;
          const str = document.getElementById('search').value;
          if ( str.length > 0 && temp.innerText.toLowerCase().includes(str.toLowerCase()) ) {
              temp.style.backgroundColor = 'green';
          } else {
              temp.style.backgroundColor = 'white';
          }
          document.getElementById("tail").appendChild(temp);
          if ( scroll ) temp.scrollIntoView(false);
      })
      pos = data.next;
      setTimeout(doTail, 5000);
    });
}
setTimeout(doTail, 500);

function doSearch() {
    const str = document.getElementById('search').value;
    // console.log('search', str);
    const parent = document.getElementById('tail');
    Array.from(parent.children).forEach((child, index) => {
        // console.log(index, child.innerText);
        if ( str.length > 0 && child.innerText.toLowerCase().includes(str.toLowerCase()) ) {
            child.style.backgroundColor = 'green';
            child.scrollIntoView(false);
        } else {
            child.style.backgroundColor = 'white';
        }
    });
}
document.getElementById('search').value='';
</script>
</body>
</html>

