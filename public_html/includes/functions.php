<?php

function read2array($filename){

$lines = array();
$file = fopen($filename, "r");
while(!feof($file)) {
    $lines[] = fgets($file, 4096);
}
fclose ($file);
return $lines;

}

?>