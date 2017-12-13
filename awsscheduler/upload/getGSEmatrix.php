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

    $sql = "SELECT gseid,gselink FROM gsematrix WHERE gseid='".$_GET["gse"]."'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        //echo $row["gseid"]." - ".$row["gselink"];
        $sql = "SELECT link, clusterid FROM clustergrammer WHERE gse='".$_GET["gse"]."'";
        $result2 = $conn->query($sql);
        
        $row2 = $result2->fetch_assoc();
        
        $arr = array ('gse'=>"".$row["gseid"],'link'=>"".$row["gselink"], 'clustergrammer'=>"http://amp.pharm.mssm.edu/clustergrammer/viz/".$row2["clusterid"]);
        echo json_encode($arr);
        //echo json_encode(array('gse'=>$row["gseid"], 'link'=>$row["gselink"]);

    }
    else{
        echo json_encode(array('gse'=>"".$_GET["gse"], 'link'=>''));
    }
}

?>