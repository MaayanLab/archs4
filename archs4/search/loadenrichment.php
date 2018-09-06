
<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';

header('Content-type: application/json');

$sql = "SELECT DISTINCT(library) FROM enrichment_terms";
$result = $conn->query($sql);
$library = [];
$genesets = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $library[] = $row["library"];
        
        $sql="SELECT DISTINCT(geneset) FROM enrichment_terms WHERE library=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $row["library"]);
        $stmt->execute();
        $stmt->bind_result($data[0]);
        $stmt->store_result();
        
        $ts = array();
        if ($stmt->num_rows > 0) {
            while ($stmt->fetch()) {
                $ts[] = $data[0];
            }
        }
        $genesets[$row["library"]] = $ts;
    }
}
echo json_encode($genesets);
?>


