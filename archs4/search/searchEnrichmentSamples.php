
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
        $species = "mouse";
    }
    else{
        $species = "human";
    }
    
    $direction = $_GET["direction"];
    if($_GET["direction"] == "up-down combined"){
        $direction = "combined";
    }
    
    #$sql = sprintf("SELECT * FROM enrichment WHERE name='%s' AND type='%s' AND species='%s'",
    #    $_GET["search"],
    #    $direction,
    #    $species);
    
    $sql = sprintf("SELECT * FROM enrichment WHERE name='%s' AND type='%s' AND species='%s'",
            $conn->real_escape_string($_GET["search"]),
            $conn->real_escape_string($direction),
            $conn->real_escape_string($species));

    $result = $conn->query($sql);
    $samples = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $val = (int)str_replace("GSM","",$row["sampleid"]);
            $samples[$val] = $val;
        }
    }
    
    $arr = array ($_GET["search"], array_values($samples), $_GET["direction"], $sql);
    echo json_encode($arr);
}

?>


