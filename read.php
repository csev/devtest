<?php

// stdbuf -i0 -o0 -e0 bash yada.sh | tee -a /tmp/zap ; echo "Fini" >> /tmp/zap

$start = $_GET['start'] ?? 0;
$file = $_GET['file'] ?? null;

$lines = array();
$fp = fopen($file, 'r');
$pos = 0;

while (($line = fgets($fp)) !== false) {
    if ( $pos >= $start ) {
        array_push($pos, rtrim($line));
        $lines[$pos] = $line;
    }
    $pos++;
}

header('Content-Type: application/json');

$retval = new \stdClass();
$retval->lines = $lines;
$retval->next = $pos;

echo(json_encode($retval));

