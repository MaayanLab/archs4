<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */

require 'dbconfig.php';

$sql = "SELECT DISTINCT gseid FROM gse WHERE gseid LIKE '%sapiens%'";
$result = $conn->query($sql);

$gsmid = "";
$samples = array();
$title = "";
$summary = "";
$authors = array();
$weblink = "";
$submissiondate = "";
$updatedate = "";
$status = "";
$contactname = "";
$organization = "";
$department = "";
$street = "";
$city = "";
$state = "";
$zip = "";
$country =  "";
$phone = "";
$email="";
$pmid = array();



if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //echo "id: " . $row["id"]. " - Name: " . $row["resultbucket"]. " " . $row["datalinks"]. " " . $row["parameters"]. " " . $row["status"]. "<br>";
        echo $row["gseid"]."<br>";
    }
}


$conn->close();


?>


