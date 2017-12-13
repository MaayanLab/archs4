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
        $sql = "SELECT * FROM tissue_expression WHERE gene='".$_GET["search"]."' AND organism='".$_GET["species"]."';";
        $result = $conn->query($sql);
        $a=array("System");
        $text = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $id = "System.".$row["system"].".".$row["tissue"].".".$row["celltype"];
                array_push($a, "System.".$row["system"].",,,,,,");
                array_push($a, "System.".$row["system"].".".$row["tissue"].",,,,,,");
                $data = $row["min"].",".$row["q25"].",".$row["median"].",".$row["q75"].",".$row["max"].",".$row["color"]."\n";
                $text = $text."$id,$data";
            }
        }
        $b=array_unique($a);
        
        echo "id,min,q1,median,q3,max,color\n";
        foreach ($b as &$value) {
            echo $value."\n";
        }
        echo $text;
    }
    else{
        $sql = "SELECT * FROM cellline_expression WHERE gene='".$_GET["search"]."';";
        $result = $conn->query($sql);
        $a=array("Cell line");
        $text = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $id = "Cell line.".$row["tissue"].".".$row["cellline"];
                array_push($a, "Cell line.".$row["tissue"].",,,,,,");
                
                $data = $row["min"].",".$row["q25"].",".$row["median"].",".$row["q75"].",".$row["max"].",".$row["color"]."\n";
                $text = $text."$id,$data";
            }
        }
        $b=array_unique($a);
        
        echo "id,min,q1,median,q3,max,color\n";
        foreach ($b as &$value) {
            echo $value."\n";
        }
        echo $text;
    }
}
?>