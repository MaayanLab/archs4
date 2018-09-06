
<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';

header('Content-type: application/json');

$sql = "SELECT DISTINCT(gene) FROM functional_prediction ORDER BY gene ASC;";

$result = $conn->query($sql);
$genes = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $genes[] = $row["gene"];
    }
}

$arr = $genes;
echo json_encode($arr);

?>


