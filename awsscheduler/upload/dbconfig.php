<?php
	
	ini_set("mysql.trace_mode", "0");
	
	$servername = "kallisto.ckjqvk8k3pqb.us-east-1.rds.amazonaws.com";
	$username = "scheduler";
	$password = getenv("sqlpass");
	$dbname = "alignment_pipeline";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
?>