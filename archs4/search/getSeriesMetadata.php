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
    
    $samples = [];
    $series = [];
    
    $esearch = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gds&term=".$_GET["search"];
    $xml = simplexml_load_file($esearch);
    $gseid = $xml->IdList->children()[0];
    
    $esummay = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=gds&id=".$gseid;
    $xml = simplexml_load_file($esummay);
    
    $ii = $xml->DocSum;
    
    $gse[] = "".$ii->Item[0];
    $gse[] = "".$ii->Item[2];
    $gse[] = "".$ii->Item[3];
    $gse[] = "GPL".$ii->Item[4];
    $gse[] = "".$ii->Item[6];
    
    $arr = $gse;
    echo json_encode($arr);
}
else{
    $arr = array ("no information");
    echo json_encode($arr);
}
?>


