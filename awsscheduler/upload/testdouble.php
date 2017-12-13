<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';


$id = $_GET["id"];

$sql = "SELECT listid FROM kallistoquant WHERE listid='$id'";
$result = $conn->query($sql);
$numr = $result->num_rows;
$conn->close();

if($_GET["mode"] == "STAR"){
    $numr = 0;
}

if($numr == 0){
    echo "doesnt exist $numr";
}
else{
    echo "exist $numr";
}

?>