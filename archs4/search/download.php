<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */


require 'dbconfig.php';


if(isset($_GET["gse"])){

	$sql = "SELECT DISTINCT gseid,attribute,value FROM gse WHERE gseid='".$_GET["gse"]."'";
	$result = $conn->query($sql);

	$newstring = "";
	$gseinfo = "";
	$gsminfo = "";

	if ($result->num_rows > 0) {

	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	    	if(substr($row["attribute"],0,1) == "^"){
				$gseinfo .= $row["attribute"]."=".$row["value"]."\n";
	    	}
	    	else{
	    		$gseinfo .= "!".$row["attribute"]."=".$row["value"]."\n";
	    	}
	    }
	}

	$sql = "SELECT DISTINCT(gsm) FROM samplemapping WHERE gse='".$_GET["gse"]."' ORDER BY gsm";
	$result = $conn->query($sql);

	$tmp_file = "";
	$gsmlist = array();

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$gsmlist[] = $row["gsm"];
	    }
	}

    $sql = "SELECT DISTINCT gsmid,attribute,value FROM gsm WHERE gsmid IN ('".implode("','",$gsmlist)."') ORDER BY gsmid,attribute DESC";
	$result = $conn->query($sql);
	
	$data = array();

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	    	$data[$row["attribute"]][] = $row["value"];
	    }
	}

	$newstring = $newstring."\n".$data[1][1]."\n";
	$keys = array_keys($data);

	for($i=0; $i<count($keys); $i++){
		if($keys[$i] == "^SAMPLE"){
			$gsminfo .= "!Sample_geo_accession";
		}
		else{
			$gsminfo .= "!".$keys[$i];
		}
		
		for($j=0; $j<count($data[$keys[$i]]); $j++){
			$gsminfo .= "\t".$data[$keys[$i]][$j];
		}
		$gsminfo .= "\n";
	}
	$gsminfo = $gsminfo."!series_matrix_table_begin\n";

	$data = array();
	for($i=0; $i<count($gsmlist); $i++){
		$sql = "SELECT kallistoquant.geneid AS geneid, genemapping.genesymbol AS genesymbol, SUM(kallistoquant.value) AS value FROM kallistoquant INNER JOIN genemapping ON kallistoquant.geneid=genemapping.geneid WHERE listid IN (SELECT listid FROM samplemapping WHERE gsm='".$gsmlist[$i]."') GROUP BY geneid";
		$result = $conn->query($sql);

		while($row = $result->fetch_assoc()) {
	    	$data[$gsmlist[$i]][$row["genesymbol"]] = round($row["value"]);
	    }
	}

	$samples = array_keys($data);
	$genes = array_keys($data[$samples[0]]);
	$gsminfo .= "ID_REF";
	for($i=0; $i<count($samples); $i++){
		$gsminfo .= "\t".$samples[$i];
	}
	$gsminfo .= "\n";

	for($i=0; $i<count($genes); $i++){
		$gsminfo .= $genes[$i];
		for($j=0; $j<count($samples); $j++){
			$gsminfo .= "\t".$data[$samples[$j]][$genes[$i]];
		}
		$gsminfo .= "\n";
	}
	$gsminfo .= "!series_matrix_table_end\n";


    $zip = new ZipArchive;
    $tmp_file = tempnam('.','');

	$res = $zip->open($tmp_file, ZipArchive::CREATE);
	if ($res === TRUE) {
	    $zip->addFromString('gse_info.txt', $gseinfo."\n".$newstring."\n".$gsminfo);
	    $zip->close();
	} else {
	}

	$conn->close();

	header('Content-disposition: attachment; filename=expression.zip');
	header('Content-type: application/zip');
	readfile($tmp_file);
}
else{
	echo "<a href='http://localhost:8888/upload/download.php?gse=GSE30017'>GSE30017</a>";
}
?>