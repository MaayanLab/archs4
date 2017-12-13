
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
    
    $search = str_replace("_", "", $_GET["search"]);
    $search = str_replace("-", "", $search);
    $search = str_replace("/", "", $search);
    $search = str_replace(" ", "", $search);
    $search = str_replace("\\.", "", $search);
    
    $sql = "SELECT gsm, gse, tissue FROM sample_meta WHERE tissue_mod LIKE '%".$search."%' AND species='".$_GET["species"]."';";
    $result = $conn->query($sql);
    $samples = [];
    $series = [];
    $tissue = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $samples[] = (int)str_replace("GSM","",$row["gsm"]);
            $series[] = $row["gse"];
            $tissue[] = $row["tissue"];
        }
    }
    
    $arr = array ($_GET["search"], $samples, $series, $tissue);
    echo json_encode($arr);
}

?>


