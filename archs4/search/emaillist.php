
<?php
    require 'dbconfig.php';
    
    if(isset($_GET["email"])){
        $sql="INSERT INTO newsletter (email) VALUES (?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_GET["email"]);
        $stmt->execute();
        $conn->close();
    }
?>