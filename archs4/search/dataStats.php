
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
    
    if($_GET["search"] == "platform"){
        
        $sql = "SELECT SUM(count) AS platcount FROM platformstat;";
        $result = $conn->query($sql);
        $speciescount = $result->fetch_assoc();
        $speciescount = $speciescount["platcount"];
        
        $allcount = 0;
        $platformid = [];
        $platformcount = [];
        $platformdesc = [];
        $platformspecies = [];
        
        $sql = "SELECT * FROM platformstat;";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $allcount = $allcount + $row["count"];
                $platformcount[] = $row["count"];
                $platformdesc[] = $row["desc"];
                $platformspecies[] = $row["species"];
                $platformid[] = $row["platform"];
            }
        }
        $arr = array ($allcount, $platformid, $platformcount, $platformdesc, $platformspecies, $speciescount);
        echo json_encode($arr);
    }
    else if($_GET["search"] == "humansamples"){
        $sql = "SELECT SUM(count) AS platcount FROM platformstat WHERE species='Human';";
        $result = $conn->query($sql);
        $speciescount = $result->fetch_assoc();
        $speciescount = $speciescount["platcount"];
        
        $sql = "SELECT * FROM tissuestat WHERE species='Human';";
        $result = $conn->query($sql);
        
        $allcount = 0;
        $tissueid = [];
        $tissuecount = [];
        $tissuespecies = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $allcount = $allcount + $row["count"];
                $tissuecount[] = $row["count"];
                $tissuespecies[] = $row["species"];
                $tissueid[] = $row["tissue"];
            }
        }
        $arr = array ($allcount, $tissueid, $tissuecount, $tissuespecies, $speciescount);
        echo json_encode($arr);
    }
    else if($_GET["search"] == "mousesamples"){
        $sql = "SELECT SUM(count) AS platcount FROM platformstat WHERE species='Mouse';";
        $result = $conn->query($sql);
        $speciescount = $result->fetch_assoc();
        $speciescount = $speciescount["platcount"];
        
        $sql = "SELECT * FROM tissuestat WHERE species='Mouse';";
        $result = $conn->query($sql);
        
        $allcount = 0;
        $tissueid = [];
        $tissuecount = [];
        $tissuespecies = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $allcount = $allcount + $row["count"];
                $tissuecount[] = $row["count"];
                $tissuespecies[] = $row["species"];
                $tissueid[] = $row["tissue"];
            }
        }
        $arr = array ($allcount, $tissueid, $tissuecount, $tissuespecies, $speciescount);
        echo json_encode($arr);
    }
    
}
?>


