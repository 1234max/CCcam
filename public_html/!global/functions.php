<?php

function read2array($filename){

$lines = array(	"client1port"=>"client1name",
							"client2port"=>"client2name",
							"client3port"=>"client3name");

$file = fopen($filename, "r");
while(!feof($file)) {
    $lines[] = fgets($file, 4096);
}
fclose ($file);
return $lines;

}

?>