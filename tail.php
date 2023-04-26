<html>
<head>
<style>
ol {
  font-family: monospace;
  white-space: pre;
}

li::marker {
  font-size: 10px;
  color: grey;
}
</style>
</head>
<body>
<ol id="tail">
</ol>
<script>
var pos = 0;
function doTail() {
  fetch('read.php?file=/tmp/zap&start='+pos)
    .then(response => response.json())
    .then(data => {
      console.log(data);
      Object.keys(data.lines).forEach(function(key) {
          console.log('Key : ' + key + ', Value : ' + data.lines[key])
          var temp = document.createElement('li');
          temp.innerHTML = data.lines[key];
          document.getElementById("tail").appendChild(temp);
      })
      pos = data.next;
      setTimeout(doTail, 5000);
    });
}
setTimeout(doTail, 1000);
</script>
</body>
</html>

