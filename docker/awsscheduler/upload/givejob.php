<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/17/16
 * Time: 3:17 PM
 */

function read_and_delete_first_line($filename) {
    $file = file($filename);
    $output = $file[0];
    unset($file[0]);
    file_put_contents($filename, $file);
    return $output;
}


echo read_and_delete_first_line("logs/waiting.log");

?>