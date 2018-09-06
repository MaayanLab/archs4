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

        #geneheader {
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
    </script>
</head>
<body>



<?php

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
    
    $sp = explode(";", $ii->Item[4]);
    
    $gpl = array_intersect (["GPL21493", "GPL21103", "GPL19057", "GPL18480", "GPL17021", "GPL15103", "GPL13112", "GPL21290", "GPL20301", "GPL18573", "GPL18460", "GPL16791", "GPL15433", "GPL11154"], $sp);
    
    $gpl[] = "empty";
    
    $species = "".$ii->Item[6];
    
    $possiblePlatforms = [];
    
    $datasets2tools = "https://amp.pharm.mssm.edu/datasets2tools/api/search?object_type=canned_analysis&dataset_accession=".$id;
    $jtext = file_get_contents($datasets2tools);
    $json = json_decode($jtext);
    
    if(isset($json[0])){
        $d2tid = $json[0]->{'canned_analysis_accession'};
    }
    
    
}

?>


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
