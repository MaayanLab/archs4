
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
    
    if($_GET["species"] == "mouse"){
        $species = "Mus Musculus";
    }
    else{
        $species = "Homo Sapiens";
    }
    
    
    $sql = "SELECT DISTINCT(gse) FROM samplemapping WHERE gsm IN (SELECT DISTINCT(gsmid) FROM gsm WHERE MATCH (value) AGAINST ('".$_GET["search"]."' IN BOOLEAN MODE));";
    $result = $conn->query($sql);
    
    $series = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $series[$row["gse"]] = $row["gse"];
        }
    }
    
    $sql = "SELECT DISTINCT(gse) FROM samplemapping WHERE gsm IN (SELECT DISTINCT(gsmid) FROM gsm WHERE attribute='Sample_organism_ch1' AND value='".$species."');";
    $result = $conn->query($sql);
    $rnaseqSeries = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $val = $row["gse"];
            $rnaseqSeries[$val] = $val;
        }
    }
    
    $tseries = array_intersect_key($series, $rnaseqSeries);
    $arr = array ($_GET["search"], array_values($tseries));
    echo json_encode($arr);
}

?>


