
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
    $search = "%".$search."%";
    
    $sql="SELECT DISTINCT(gse) FROM sample_meta WHERE tissue_mod LIKE ? AND species = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', "%".$search."%", $_GET["species"]);
    $stmt->execute();
    $stmt->bind_result($data[0]);
    $stmt->store_result();
    
    $series = [];
    if ($stmt->num_rows > 0) {
        while($stmt->fetch()) {
            $series[$data[0]] = $data[0];
        }
    }
    
    $arr = array ($_GET["search"], array_values($series));
    echo json_encode($arr);
}

?>


