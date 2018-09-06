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
    
    $sql="SELECT gseid, platform, gselink, jsonlink FROM gsematrix WHERE gseid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $_GET["gse"]);
    $stmt->execute();
    $stmt->bind_result($data[0], $data[1], $data[2], $data[3]);
    $stmt->store_result();
    
    if($stmt->num_rows > 0){
        $arr = array();
        while ($stmt->fetch()) {
            $arr[] = array ('gse'=>"".$data[0],'platform'=>"".$data[1],'ziplink'=>"".$data[2], 'jsonlink'=>$data[3]);
        }
        echo json_encode($arr);
    }
    else{
        echo json_encode(array('gse'=>"".$_GET["gse"], 'ziplink'=>'', 'jsonlink'=>''));
    }
}

?>