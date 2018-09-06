
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
    
    $sql = "SELECT DISTINCT(gsmid) FROM gsm WHERE MATCH (value) AGAINST ('".$_GET["search"]."' IN BOOLEAN MODE);";
    $result = $conn->query($sql);
    $samples = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $val = (int)str_replace("GSM","",$row["gsmid"]);
            $samples[$val] = $val;
        }
    }
    
    $sql = "SELECT DISTINCT(gsmid) FROM gsm WHERE attribute='Sample_organism_ch1' AND value='".$species."'";
    $result = $conn->query($sql);
    $speciesSamples = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $val = (int)str_replace("GSM","",$row["gsmid"]);
            $speciesSamples[$val] = $val;
        }
    }
    
    $sql = "SELECT DISTINCT(gsm) FROM samplemapping;";
    $result = $conn->query($sql);
    $rnaseqSamples = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $val = (int)str_replace("GSM","",$row["gsm"]);
            $rnaseqSamples[$val] = $val;
        }
    }
    
    $tsamples = array_intersect_key($samples, $rnaseqSamples);
    $fsamples = array_intersect_key($tsamples, $speciesSamples);
    
    $arr = array ($_GET["search"], array_values($fsamples));
    echo json_encode($arr);
}

?>


