
<?php
    require 'dbconfig.php';
    
    if(isset($_GET["file"])){
        $sql="INSERT INTO downloadcounts (ip, file, version) VALUES (?, ?, ?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $_GET["ip"], $_GET["file"], $_GET["version"]);
        $stmt->execute();
        $conn->close();
    }
    
    if(isset($_GET["samplenumber"])){
        $sql="INSERT INTO filedownloadinfo (searchterm, species, numbersamples, userip) VALUES (?, ?, ?, ?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $_GET["searchterm"], $_GET["species"], $_GET["samplenumber"], $_GET["ip"]);
        $stmt->execute();
        $conn->close();
    }
?>