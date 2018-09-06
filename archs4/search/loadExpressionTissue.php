<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */
require 'dbconfig.php';
if(isset($_GET["search"])){
    
    if($_GET["type"] == "tissue"){
        
        $a=array("System");
        $text = "";
        
        $sql="SELECT system, tissue, celltype, min, q25, median, q75, max, color FROM tissue_expression WHERE gene=? AND organism=?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $_GET["search"], $_GET["species"]);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8]);
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $id = "System.".$data[0].".".trim($data[1]).".".trim($data[2]);
                array_push($a, "System.".$data[0].",,,,,,");
                array_push($a, "System.".$data[0].".".trim($data[1]).",,,,,,");
                $dat = $data[3].",".$data[4].",".$data[5].",".$data[6].",".$data[7].",".$data[8]."\n";
                $text = $text."$id,$dat";
            }
        }
        $b=array_unique($a);
        
        echo "id,min,q1,median,q3,max,color\n";
        foreach ($b as &$value) {
            echo $value."\n";
        }
        echo trim($text);
    }
    else{
        
        $a=array("Cell line");
        $text = "";
        
        $sql="SELECT tissue, cellline, min, q25, median, q75, max, color FROM cellline_expression WHERE gene=?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_GET["search"]);
        $stmt->execute();
        $stmt->bind_result($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]);
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $id = "Cell line.".$data[0].".".trim($data[1]);
                array_push($a, "Cell line.".$data[0].",,,,,");
                $dat = $data[2].",".$data[3].",".$data[4].",".$data[5].",".$data[6].",".$data[7]."\n";
                $text = $text."$id,$dat";
            }
        }
        $b=array_unique($a);
        
        echo "id,min,q1,median,q3,max,color\n";
        foreach ($b as &$value) {
            echo $value."\n";
        }
        echo trim($text);
    }
}
?>