
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
    
    $sql = sprintf("SELECT * FROM genelists WHERE term='%s'", $conn->real_escape_string($_GET["search"]));
    
    $result = $conn->query($sql);
    $genes = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $genes[] = $row["gene"];
        }
    }
    
    $arr = array ($_GET["search"], $genes, $sql);
    echo json_encode($arr);
}
?>