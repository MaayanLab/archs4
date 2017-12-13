<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */


require 'dbconfig.php';


$sql = "SELECT * FROM gse WHERE gseid='GSE88858'";
$result = $conn->query($sql);

$gseid = "";
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
        
        if(strcmp($row["attribute"], "Series_sample_id") == 0){
            $samples[] = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contributor") == 0){
            $authors[] = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_title") == 0){
            $title = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_summary") == 0){
            $summary = $row["value"];
        }
        elseif(strcmp($row["attribute"], "^SERIES") == 0){
            $gseid = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_institute") == 0){
            $organization = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_address") == 0){
            $street = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_city") == 0){
            $city = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_state") == 0){
            $state = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_zip/postal_code") == 0){
            $zip = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_country") == 0){
            $country = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_department") == 0){
            $department = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_phone") == 0){
            $phone = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_email") == 0){
            $email = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_contact_name") == 0){
            $contactname = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_status") == 0){
            $status = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_submission_date") == 0){
            $submissiondate = $row["value"];
        }
        elseif(strcmp($row["attribute"], "Series_last_update_date") == 0){
            $updatedate = $row["value"];
        }
    }
}

$authors = array_unique($authors);
$samples = array_unique($samples);

echo "<link href=\"css/styles.css\" type=\"text/css\" rel=\"stylesheet\" />";

echo "<div id=gse>";

echo "<div id=maininfo>";
echo "<div id=gseid>".$gseid."</div>";
echo "<div id=gsetitle>".$title."</div>";
echo "<div id=gsesummary>".$summary."</div>";
echo "<div id=authors>".implode(", ",$authors)."</div>";
echo "<br>";
echo "<div id=samples>".implode(", ",$samples)."</div>";
echo "</div>";

echo "<div id=detail>";
echo "<div id=samplecount>Samples: ".count($samples)."</div>";
echo "<div id=contact>".$contactname."</div>";
echo "<div id=email>".$email."</div>";
echo "<div id=email>".$phone."</div>";
echo "<div id=department>".$department."</div>";
echo "<div id=institution>".$organization."</div>";
echo "<div id=street>".$street."</div>";
echo "<div id=city>".$zip." ".$city."</div>";
echo "<div id=country>".$country."</div>";
echo "</div>";

echo "<div id=dates>";
echo "<div id=status>".$status."</div>";
echo "<div id=updatedate>".$updatedate."</div>";
echo "<div id=submissiondate>".$submissiondate."</div>";
echo "</div>";

echo "</div>";


$conn->close();


?>






