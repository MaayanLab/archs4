<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */
require 'dbconfig.php';
header('Content-type: application/json');
if(isset($_GET["search"])){
    
    $samples = [];
    $series = [];
    
    if(preg_match('/^GSE[0-9]{4,10}$/', $_GET["search"]) == 1){
        $sql="SELECT gsm, gse FROM sample_meta WHERE gse=? AND species=?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $_GET["search"], $_GET["species"]);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1]);
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $samples[] = (int)str_replace("GSM", "", $data[0]);
                $series[] = $data[1];
            }
        }
    }
    elseif(preg_match('/^GSM[0-9]{4,10}$/', $_GET["search"]) == 1){
        
        $sql="SELECT gsm, gse FROM sample_meta WHERE gse IN (SELECT gse FROM sample_meta WHERE gsm=? AND species=?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $_GET["search"], $_GET["species"]);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1]);
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $samples[] = (int)str_replace("GSM", "", $data[0]);
                $series[] = $data[1];
            }
        }
    }
    else{
        $search = str_replace("_", "", $_GET["search"]);
        $search = str_replace("-", "", $search);
        $search = str_replace("/", "", $search);
        $search = str_replace(" ", "", $search);
        $search = str_replace("\\.", "", $search);
        $search = "%".$search."%";
        
        $sql="SELECT gsm, gse FROM sample_meta WHERE tissue_mod LIKE ? AND species=?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $search, $_GET["species"]);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1]);
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $samples[] = (int)str_replace("GSM", "", $data[0]);
                $series[] = $data[1];
            }
        }
    }
    $arr = array ($_GET["search"], $samples, $series);
    echo json_encode($arr);
}
?>