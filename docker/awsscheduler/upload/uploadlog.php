<?php
    /**
     * Created by PhpStorm.
     * User: maayanlab
     * Date: 9/17/16
     * Time: 2:29 PM
     */

    $filename = $_GET["fileid"];

    $file = 'logs/upload.log';
    $current = file_get_contents($file);
    $current .= $filename."\n";
    file_put_contents($file, $current);

?>