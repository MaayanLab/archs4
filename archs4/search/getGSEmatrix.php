<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */


require 'dbconfig.php';

header('Content-type: application/json');

if(isset($_GET["gse"])){

    $sql = "SELECT gseid,platform,gselink,jsonlink FROM gsematrix WHERE gseid='".$_GET["gse"]."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $arr = array();
        while($row = $result->fetch_assoc()) {
        //echo $row["gseid"]." - ".$row["gselink"];
            $arr[] = array ('gse'=>"".$row["gseid"],'platform'=>"".$row["platform"],'ziplink'=>"".$row["gselink"], 'jsonlink'=>$row["jsonlink"]);
        }
        echo json_encode($arr);
        //echo json_encode(array('gse'=>$row["gseid"], 'link'=>$row["gselink"]);
    }
    else{
        echo json_encode(array('gse'=>"".$_GET["gse"], 'ziplink'=>'', 'jsonlink'=>''));
    }
}

?>