<?php
var_dump($argv);
if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {

    echo $argv[0];
} else {
    echo $argv[1];  
}
?>