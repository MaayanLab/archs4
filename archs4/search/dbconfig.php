<?php
	
	ini_set("mysql.trace_mode", "0");
	
	$servername = getenv("dbserver");
    $username = getenv("dbuser");
    $password = getenv("dbpass");
    $dbname = getenv("dbname");
    
    
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
?>