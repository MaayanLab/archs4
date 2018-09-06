<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Alexander Lachmann">
    <title>ARCHS4</title>

    <link rel="icon" href="../images/archs-icon.png?v=2" type="image/png">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
    <script src="https://d3js.org/d3.v4.min.js"></script>

    <script src="../scripts/jquery.auto-complete.min.js"></script>
    <link rel="stylesheet" href="../css/jquery.auto-complete.css">

    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" href="../css/css.css">
    <link rel="stylesheet" type="text/css" href="../css/footer.css">
    <link rel="stylesheet" type="text/css" href="../css/desert.css">

    <style>

        #seriesheader {
            width:100%;
            box-sizing:border-box;
            background-color: white;
            z-index: 999;

            position: fixed; /* Set the navbar to fixed position */
            top: 0; /* Position the navbar at the top of the page */
        }

    </style>

    <script type="text/javascript">
        if (location.protocol != 'https:'){
            //location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
        }
        
        function scrollPage(location){
            $('html, body').animate({
                scrollTop: $("#"+location).offset().top - $("#geneheader").height() - 40
            }, 1000);
        }

        function submitGeneSearch(){
            var geneid = $("#genesymbolsearch").val().toUpperCase();
            if(geneid.length > 1){
                window.open("genepage.php?search=go&gene="+geneid,"_self");
            }
        }
        
        $(document).ready(function(){
            $('#tablecor').DataTable({
                "iDisplayLength": 25
            });
        });
    </script>
</head>
<body>

<?php

require 'dbconfig.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_GET["search"])){
    
    $esearch = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gds&term=".$_GET["search"];
    $xml = simplexml_load_file($esearch);
    $gseid = $xml->IdList->children()[0];
    
    $esummay = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=gds&id=".$gseid;
    $xml = simplexml_load_file($esummay);
    
    $ii = $xml->DocSum;
    $id = "".$ii->Item[0];
    $title = "".$ii->Item[2];
    $summary = "".$ii->Item[3];
    
    $samplelist = $ii->Item[15];
    
    $sampleids = [];
    $sampledesc = [];
    $samplef = [];
    for($i=0; $i<count($samplelist->Item); $i++){
        $sampleids[] = $samplelist->Item[$i]->Item[0];
        $sampledesc[] = $samplelist->Item[$i]->Item[1];
        $samplef["".$samplelist->Item[$i]->Item[0]] = $samplelist->Item[$i]->Item[1];
    }
    
    $impsamples = "'".implode("','", $sampleids)."'";
    
    $sql="SELECT gsm, runinfo.listid, parameters, SUM(nreads), SUM(naligned) FROM samplemapping INNER JOIN runinfo ON samplemapping.listid=runinfo.listid INNER JOIN sequencing ON samplemapping.listid=sequencing.id WHERE samplemapping.gsm IN (".$impsamples.") GROUP BY gsm;";
    #$sql = "SELECT listid, gse, gsm FROM samplemapping WHERE gsm IN (".$impsamples.") GROUP BY gsm;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $stmt->bind_result($data[0], $data[1], $data[2], $data[3], $data[4]);
    
    $sp = explode(";", $ii->Item[4]);
    $gpl = array_intersect(["21493", "21103", "19057", "18480", "17021", "15103", "13112", "21290", "20301", "18573", "18460", "16791", "15433", "11154"], $sp);
    
    $cgpl = [];
    foreach($gpl as &$g){
        $cgpl[] = "GPL".$g;
    }
    $gpl = $cgpl;
    
    $species = "".$ii->Item[6];
    
    $possiblePlatforms = [];
    
    $datasets2tools = "https://amp.pharm.mssm.edu/datasets2tools/api/search?object_type=canned_analysis&dataset_accession=".$id;
    $jtext = file_get_contents($datasets2tools);
    $json = json_decode($jtext);
    
    if(isset($json[0])){
        $d2tid = $json[0]->{'canned_analysis_accession'};
    }
    
    echo "<div id=\"seriesheader\" style=\"background-color: #ffffff; padding: 20px; padding-top:0px; padding-bottom: 6px; border-bottom: 2px solid darkgrey; position: fixed; overflow: hidden; top: 0;\">";
    echo "<h2 style=\"float: left;\"><a href=\"https://amp.pharm.mssm.edu/archs4\" target=\"_blank\"><img src=\"../images/archs-42.png\" style=\"height: 40px;\"></a> <img src=\"../images/labflask.png\" style=\"height: 40px;\"> ".$id."</span></h2>";
    
    foreach($gpl as &$g){
        echo "<form action=\"https://amp.pharm.mssm.edu/biojupies/analyze/tools\" method=\"post\" target=\"_blank\">";
        echo "<input type=\"hidden\" name=\"gse-gpl\" value=\"".$id."-".$g."\">";
        echo "<div style=\"float: right; padding: 10px; background-color: #cccccc; margin: 10px 0px 10px 10px; width: 348px;\"><button type=\"submit\" class=\"btn btn-primary btn-lg\"><img src=\"../images/biojupies.png\" height=40> Differential gene expression >></button><br><br>BioJupies allows the generation of differential gene expression signatures as well as other data exploration methods on GEO data sets such as heatmaps and PCA plots.";
        echo "<div style=\"padding-top: 10px; background-color: #cccccc; width: 100%;\">";
        echo "<b>Download gene counts:</b><br>";
        echo "<a href=\"https://s3.amazonaws.com/mssm-seq-series-platform-gz/".$id."-".$g."_series_matrix.txt.gz\">".$id."-".$g."_series_matrix.txt.gz</a></div></div>";
        echo "</form>";
    }
    
    echo "<br><br><br><br><div sytle=\"text-align: center\"><h3>".$title."</h3></div>";
    echo "<div>";
    echo "<div><b>Summary: </b>".$summary."</div><br><div style=\"float: left;\"><div><b>GEO:</b> <a href=\"https://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=".$id."\" target=\"_blank\">https://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=".$id."</a></div>";
    if(isset($d2tid)){
        echo "<div><b>Datasets2Tools:</b> <a href=\"https://amp.pharm.mssm.edu/datasets2tools/landing/canned_analysis/".$d2tid."\"  target=\"_blank\">https://amp.pharm.mssm.edu/datasets2tools/landing/canned_analysis/".$d2tid."</a></div>";
    }
    
    $scavi = file_get_contents('https://amp.pharm.mssm.edu/scavi/graph_page/'.$_GET["search"].'/tSNE/3');
    if(substr( $scavi, 0, 3 ) === "<!d"){
        echo "<div><b>SCAVI:</b> <a href=\"https://amp.pharm.mssm.edu/scavi/graph_page/".$_GET["search"]."/tSNE/3\"  target=\"_blank\">https://amp.pharm.mssm.edu/scavi/graph_page/".$_GET["search"]."/tSNE/3</a></div>";
    }
    
    echo "</div><div style=\"float: right;\">";
    foreach($gpl as &$g){
        echo "<div ><b>Platform:</b> ".$g."</div>";
    }
    echo "<div><b>Species:</b> ".$species."</div><br>";
    echo "</div></div></div><div id=\"bound\" style=\"padding: 20px;\">";
    
    echo "<table id=\"tablecor\" class='table table-striped table-bordered'><thead><tr><th>Sample GSM ID</th><th>Title</th><th>Organism</th><th>Library size</th></tr></thead><tbody>";
    while ($stmt->fetch()) {
        echo "<tr><td>".$data[0]."</td><td>".$samplef[$data[0]]."</td><td>".str_replace("organism:","",$data[2])."</td><td>".$data[4]."</td></tr>";
    }
    echo "</tbody></table>";
    echo "</div>";
}

?>

<!--
<iframe style="width: 100%; border: none; height: 600px; border-top: 1px solid grey;" src = "https://amp.pharm.mssm.edu/scavi/graph_page/GSE81547/tSNE/3"></iframe>
-->


<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-6277639-31', 'auto');
    ga('send', 'pageview');
    
    $('body').css({'margin-top': (8 + $('#seriesheader').height())+'px'});
    
    $(window).resize(function(){
        $('body').css({'margin-top': (8 + $('#seriesheader').height())+'px'});
        
    });

</script>



</body>
</html>
