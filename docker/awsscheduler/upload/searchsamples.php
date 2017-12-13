
<form action="searchsamples.php" method="get">
  Search samples: <input type="text" name="search" size=50>
  
  <input type="submit" value="Submit">
</form>

<?php

	require 'dbconfig.php';
	
	if(isset($_GET["search"])){

		if(isset($_GET["from"])){
			$from = $_GET["from"];
		}
		else{
			$from = 1;
		}

		$stepsize = 20;
		$from = ($from-1)*$stepsize+1;
		$to = $from + $stepsize-1;

		//$parts = preg_split('/\s+/', $_GET["search"]);
		//$sql = "SELECT SQL_CALC_FOUND_ROWS gsmid FROM gsm WHERE MATCH (value) AGAINST ('".$parts[0]."' IN BOOLEAN MODE)";
		
		//for($i=1; $i < count($parts); $i++){
		//	$sql = $sql. " AND MATCH (value) AGAINST ('".$parts[$i]."' IN BOOLEAN MODE)";
		//}
		//$sql = $sql. " LIMIT ".$from.",".$to.";";
		$searchterm = "+".str_replace(" "," +",str_replace("-"," ",$_GET["search"]));

		$sql = "SELECT SQL_CALC_FOUND_ROWS gsmid FROM gsm WHERE MATCH (value) AGAINST ('".$searchterm."' IN BOOLEAN MODE) AND  LIMIT ".$from.",".$stepsize.";";
		//$sql = "Select SQL_CALC_FOUND_ROWS gsmid from gsm where value like'%".$_GET["search"]."%' LIMIT ".$from.",".$to.";";
		echo "$sql";

		$result = $conn->query($sql);
		$total_records = $conn->query('SELECT FOUND_ROWS()')->fetch_assoc()['FOUND_ROWS()'];
		echo "<h3>Results for: ".$_GET["search"]."<br>Hits: ".$total_records."</h3><br>";
		echo "$from - $to <br>";
		$steps = round($total_records/$stepsize, 0, PHP_ROUND_HALF_DOWN);

		if(max(1, $_GET["from"]-5) != 1){
			echo "... ";
		}

		for($i=max(1, $_GET["from"]-5); $i<min($steps,$_GET["from"]+6); $i++){
			
			if($i == $_GET["from"]){
				echo "<b>".$i."</b> ";
			}
			else{
				echo "<a href=searchsamples.php?search=".$_GET["search"]."&from=".$i.">$i</a> ";
			}
		}

		if($i = min($steps,$_GET["from"]+6)){
			echo "... ";
		}

		echo "<br><br>";

		$i = 0;
		if ($result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
		    	$i++;
		        //echo "id: " . $row["id"]. " - Name: " . $row["resultbucket"]. " " . $row["datalinks"]. " " . $row["parameters"]. " " . $row["status"]. "<br>";
		        echo "$i - ".$row["gsmid"]." - ".$row["value"]."<br>";
		    }
		}
	}

?>


