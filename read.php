<?php

// echo "Start" >> /tmp/zap ; stdbuf -i0 -o0 -e0 bash yada.sh | tee -a /tmp/zap ; echo "Fini" >> /tmp/zap

$start = $_GET['start'] ?? 1;
$limit = $_GET['limit'] ?? -1;
$file = $_GET['file'] ?? null;
if ( $start < 1 ) $start = 1;

$lines = array();
$fp = fopen($file, 'r');
$pos = 1;
$shown = 0;
if ( $limit < 10000) $limit = 10000;

while (($line = fgets($fp)) !== false) {
    if ( $pos >= $start ) {
        $thing = array($pos, $line);
        array_push($thing);
        $lines[$pos] = $line;
        $shown++;
        if ( $limit > 0 && $shown >= $limit ) break;
    }
    $pos++;
}

header('Content-Type: application/json');

$retval = new \stdClass();
$retval->lines = $lines;
$retval->next = $pos+1;

echo(json_encode($retval));

