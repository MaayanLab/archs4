<?php
/**
 * Created by PhpStorm.
 * User: maayanlab
 * Date: 9/30/16
 * Time: 12:18 PM
 */
require 'dbconfig.php';
if(isset($_GET["search"])){
    
    $description = "No gene information availbale.";
    $sql = "SELECT * FROM gene_info WHERE gene='".$_GET["gene"]."';";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $description = $row["description"];
        }
    }
    
    echo "<div style=\"width:1020px; overflow: hidden;\"><div style=\"float: left; width: 600px;\"><h2><img src=\"images/dnaicon2.png\"><span id=\"genesy\">".$_GET["gene"]."</span> <a href=\"search/genepage.php?search=go&gene=".$_GET["gene"]."\" target=\"_blank\"> <i class=\"glyphicon glyphicon-share-alt\" style=\"cursor:pointer; font-size: 23;\"></i></a></h2></div><div style=\"padding-top:24; overflow: hidden; flow: right;\"><a href=# onclick=openGenepage2('".$_GET["gene"]."','go')>Functional Prediction</a> | <a href=# onclick=loadDendrogram('human','".$_GET["gene"]."','tissue')>Tissue Expression</a> | <a href=# onclick=loadDendrogram('human','".$_GET["gene"]."','cellline')>Cell Line Expression</a></div>";
    echo "<br><br><div id=\"genedesc\">".$description."</div><br><div id=\"anchorlinks\" style=\"margin-left:300px;\"><a href=# onclick=openGenepage2('".$_GET["gene"]."','go')>GO</a> | <a href=# onclick=openGenepage2('".$_GET["gene"]."','ke')>Kegg</a> | <a href=# onclick=openGenepage2('".$_GET["gene"]."','mgi')>MGI phenotype</a> | <a href=# onclick=openGenepage2('".$_GET["gene"]."','hp')>Human phenotype</a> | <a href=# onclick=openGenepage2('".$_GET["gene"]."','che')>ChEA</a> | <a href=# onclick=openGenepage2('".$_GET["gene"]."','kea')>KEA</a></div></div><hr>";
    
    echo "<div id=\"header_bio\">";
    if($_GET["search"] == "go"){
         echo "<h3>Predicted biological processes (GO)</h3>";
    }
    else if($_GET["search"] == "che"){
        echo "<h3>Predicted upstream transcription factors (ChEA)</h3>";
    }
    else if($_GET["search"] == "mgi"){
        echo "<h3>Predicted mouse phenotypes (MGI)</h3>";
    }
    else if($_GET["search"] == "hp"){
        echo "<h3>Predicted human phenotypes</h3>";
    }
    else if($_GET["search"] == "kea"){
        echo "<h3>Predicted kinase interactions (KEA)</h3>";
    }
    else if($_GET["search"] == "ke"){
        echo "<h3>Predicted pathways (KEGG)</h3>";
    }
    echo "</div>";
    
    echo "<div id=\"gene_page_content\" style=\"overflow:scroll; width:1030px; height:380px;\">";
    echo "<div class=\"container\" style=\"width:1000px;\">";
    
    echo "<div id=\"wrapper\"><div id=\"first\">";
    echo "<div id=\"tableContainer\" class=\"tableContainer\">";
    echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"table table-bordered table-hover table-condensed table-striped scrollTable\">";
    echo "<thead class=\"fixedHeader\">";
    echo "<tr><th>Rank</th><th>GO term</th><th>Z-score</th></tr>";
    echo "</thead>";
    echo "<tbody class=\"scrollContent\">";
    
    $sql = "SELECT * FROM functional_prediction WHERE listtype='".$_GET["search"]."' AND gene='".$_GET["gene"]."' GROUP BY geneset ORDER BY zscore DESC;";
    $result = $conn->query($sql);
    $ii = 1;
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if($row["termmatch"] == 1){
                echo "<tr style=\"background-color: #93ffe9; font-weight: bold;\"><td>".$ii."</td><td>* ".$row["geneset"]."</td><td align=\"right\">".$row["zscore"]."</td></tr>\n";
            }
            else{
                echo "<tr><td>".$ii."</td><td>".$row["geneset"]."</td><td align=\"right\">".$row["zscore"]."</td></tr>\n";
            }
            $ii = $ii+1;
        }
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "<div id=\"second\">";
    
    if($_GET["search"] == "go"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_go_bio_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    else if($_GET["search"] == "che"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_chea_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    else if($_GET["search"] == "mgi"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_mgi_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    else if($_GET["search"] == "hp"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_humph_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    else if($_GET["search"] == "kea"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_kea_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    else if($_GET["search"] == "ke"){
        echo "<div id=\"missingtext\"></div>";
        echo "<img src=\"images_roc/roc_kegg_".$_GET["gene"].".png\" height=\"320\" width=\"320\" onerror=\"imgError(this);\"/>";
    }
    
    echo "</div>";
    echo "</div>";
    echo "</div></div>";
    
    echo "<p><a data-popup-close=\"popup-1\" href=\"#\">Close</a></p><a class=\"popup-close\" data-popup-close=\"popup-1\" href=\"#\">x</a></p>";
}
?>