
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
        
        $sql = "SELECT DISTINCT(geneset) FROM enrichment_terms WHERE library='".$row["library"]."'";
        $resu = $conn->query($sql);
        $ts = array();
        
        if ($resu->num_rows > 0) {
            while($ro = $resu->fetch_assoc()) {
                $ts[] = $ro["geneset"];
            }
        }
        
        $genesets[$row["library"]] = $ts;
        
        #$genesets = array_push($genesets, $ts);
        #array_push($genesets, $ts);
    }
}

echo json_encode($genesets);


?>


