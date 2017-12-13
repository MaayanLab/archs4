<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';


function fast_array_diff($a, $b) {
    $map = array();
    foreach($a as $val) $map[$val] = 1;
    foreach($b as $val) unset($map[$val]);
    return array_keys($map);
}

$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
$id = $data["id"];
$uid = $data["uid"];

$nreads = $data["nreads"];
$naligned = $data["naligned"];
$nlength = $data["nlength"];

$upgenesymbols = array_map('strtoupper', $data["genesymbols"]);
$values = $data["values"];


$sql = "SELECT listid FROM kallistoquant WHERE listid='$id'";
if($_GET["mode"] == "STAR"){
    $sql = "SELECT listid FROM star_quant WHERE listid='$id'";
}
$result = $conn->query($sql);

$numr = $result->num_rows;

$sql = "UPDATE sequencing SET status='completed', datecompleted=now() WHERE id='$id' AND uid='$uid'";
if($_GET["mode"] == "STAR"){
    $sql = "UPDATE star_sequencing SET status='completed' WHERE id='$id' AND uid='$uid'";
}
$conn->query($sql);


if($numr == 0){
    
    # get existing gene symbol mappings
    $sql = "SELECT genesymbol FROM genemapping";
    if($_GET["mode"] == "STAR"){
        $sql = "SELECT genesymbol FROM star_genemapping";
    }
    $result = $conn->query($sql);
    
    $genesymbols = array("starterxxx");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $genesymbols[] = $row["genesymbol"];
        }
    }

    $newsymbols = fast_array_diff($upgenesymbols, $genesymbols);

    if (count($newsymbols) > 0) {
        $sql = "INSERT INTO genemapping (genesymbol) VALUES ('";
        if($_GET["mode"] == "STAR"){
            $sql = "INSERT INTO star_genemapping (genesymbol) VALUES ('";
        }
        $gs = implode("'),('", $newsymbols);
        $sql .= $gs."')";
        $result = $conn->query($sql);
    }

    // get existing gene symbol mappings
    $sql = "SELECT * FROM genemapping";
    if($_GET["mode"] == "STAR"){
        $sql = "SELECT * FROM star_genemapping";
    }
    $result = $conn->query($sql);
    
    $genesymbols = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $genesymbols[$row["genesymbol"]] = $row["geneid"];
        }
    }
    
    $sql = "INSERT INTO kallistoquant (listid, geneid, value) VALUES ";
    if($_GET["mode"] == "STAR"){
        $sql = "INSERT INTO star_quant (listid, geneid, value) VALUES ";
    }
    for ($i=0; $i < 20000; $i++) {
        $sql .= "('".$id."','".$genesymbols[$upgenesymbols[$i]]."','".$values[$i]."'),";
    }
    $sql = rtrim($sql, ',');
    
    $result = $conn->query($sql);
    
    $sql = "INSERT INTO kallistoquant (listid, geneid, value) VALUES ";
    if($_GET["mode"] == "STAR"){
        $sql = "INSERT INTO star_quant (listid, geneid, value) VALUES ";
    }
    for ($i=20000; $i < count($upgenesymbols); $i++) {
        $sql .= "('".$id."','".$genesymbols[$upgenesymbols[$i]]."','".$values[$i]."'),";
    }
    $sql = rtrim($sql, ',');

    $result = $conn->query($sql);


    $sql = "INSERT INTO runinfo (listid, nreads, naligned, nlength) VALUES ('$id', '$nreads', '$naligned', '$nlength')";
    
    if($_GET["mode"] == "STAR"){
        $sql = "INSERT INTO star_runinfo (listid, nreads, naligned, nlength) VALUES ('$id', '$nreads', '$naligned', '$nlength')";
    }
    $conn->query($sql);
    
}

?>












