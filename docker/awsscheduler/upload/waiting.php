<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/17/16
 * Time: 3:01 PM
 */



$filename = $_GET["fileid"];

$file = 'logs/waiting.log';
$current = file_get_contents($file);
$current .= $filename."\n";
file_put_contents($file, $current);



?>