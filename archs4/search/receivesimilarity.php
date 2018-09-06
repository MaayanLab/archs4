<meta charset="utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='expires' content='0'>
<meta http-equiv='pragma' content='no-cache'>
<script>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_GET["url"])){
    $url = $_GET["url"];
    $organism = $_GET["organism"];
    
    echo "sessionStorage.setItem(\"similarity\", \"".$url."\");";
    echo "sessionStorage.setItem(\"organism\", \"".$organism."\");";
}

?>

var win = window.open("../data.html?sim=1","_self");
</script>
