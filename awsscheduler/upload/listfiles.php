<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/16/16
 * Time: 5:46 PM
 */

$dir = 'uploads';

if (is_dir($dir)){
    if ($dh = opendir($dir)){
        while (($file = readdir($dh)) !== false){
            if($file != ".." & $file != ".DS_Store" & $file != "." &$file != "upload.log" ) {
                echo $file . "<br>";
            }
        }
        closedir($dh);
    }
}



?>