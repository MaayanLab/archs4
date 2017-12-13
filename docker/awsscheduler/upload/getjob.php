<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */


require 'dbconfig.php';

$sql = "SELECT * FROM sequencing WHERE status='waiting' LIMIT 1";

if($_GET["mode"] == "STAR"){
    $sql = "SELECT * FROM star_sequencing WHERE status='waiting' LIMIT 1";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       
        $obj = ['id' => $row["id"],
            'uid' => $row["uid"],
            'type' => "sequencing",
            'resultbucket' => $row["resultbucket"],
            'datalinks' => $row["datalinks"],
            'parameters' => $row["parameters"]];

        $j = json_encode($obj);
        echo $j;

        $id = $row["id"];
        $sql = "UPDATE sequencing SET status='submitted', datesubmitted=now() WHERE id='$id'";

        if($_GET["mode"] == "STAR"){
            $sql = "UPDATE star_sequencing SET status='submitted' WHERE id='$id'";
        }

        if ($conn->query($sql) === TRUE) {

        } else {
            echo "Error updating record: " . $conn->error;
        }

    }
} else {
    $obj = ['id' => "empty"];
    $j = json_encode($obj);
    echo $j;
}
$conn->close();


?>