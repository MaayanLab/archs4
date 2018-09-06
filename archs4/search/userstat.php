
<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';

header('Content-type: application/json');


$jdata = [];

$sql = "SELECT MAX(id) as counts FROM genesearchinfo;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$jdata["gene_search_count"] = $row["counts"];

$sql = "SELECT MAX(id) as counts FROM downloadcounts";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$jdata["bulk_download"] = $row["counts"];

$sql = "SELECT MAX(id) as counts FROM filedownloadinfo;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$jdata["sample_download"] = $row["counts"];

echo json_encode($jdata);

?>


