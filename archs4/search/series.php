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

    <script src="../scripts/saveSvgAsPng.js"></script>


    <style>

        #geneheader {
            width:100%;
            box-sizing:border-box;
            background-color: white;
            z-index: 999;

            position: fixed; /* Set the navbar to fixed position */
            top: 0; /* Position the navbar at the top of the page */
        }

        .main {
            margin-top: 220px; /* Add a top margin to avoid content overlay */
        }
    </style>

    <script type="text/javascript">
        if (location.protocol != 'https:'){
         //location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
        }
        var genes = "";
        var gene = "";
        function imgErrorGenepage(obj){
            obj.parentNode.innerHTML = "Not enough gene annotations available.";
            //obj.parentNode.removeChild(obj);
        }

        function loadCorrelation(gene1){

            gene = gene1;
            var jsonData = {};

            jsonData["gene"] = gene;

            $.ajax({
                type: "POST",
                url: "https://amp.pharm.mssm.edu/custom/rooky",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                data: JSON.stringify(jsonData),
                success: function(jdata) {
                    var samples = jdata;

                    var genesym = [];
                    var correlation = {};

                    //1) combine the arrays:
                    var list = [];
                    for (var j = 0; j < samples[0].length; j++){
                        list.push({'genesym': samples[0][j], 'cor': samples[1][j]});
                    }

                    //2) sort:
                    list.sort(function(a, b) {
                        return ((a.cor > b.cor) ? -1 : ((a.cor == b.cor) ? 0 : 1));
                        //Sort could be modified to, for example, sort on the age
                        // if the name is the same.
                    });

                    //3) separate them back out:
                    genes = "";
                    var corrinfo = {};
                    var tabletext = "<table id='tablecor' class='table table-striped table-bordered'><thead><tr><th>Rank</th><th>Gene Symbol</th><th>Pearson Correlation</th></tr><tbody>";
                    for (var k = 1; k < 101; k++) {
                        tabletext += "<tr><td>"+k+"</td><td><a href=\"genepage.php?search=go&gene="+list[k].genesym+"\" target=\"_blank\">"+list[k].genesym+"</a></td><td>"+list[k].cor+"</td></tr>";
                        genes = genes+list[k].genesym+"\n";
                        corrinfo[list[k].genesym] = list[k].cor;
                    }
                    tabletext += "</tbody></table>";
                    console.log(corrinfo);
                    geneinfovar["correlation"] = corrinfo;
                    document.getElementById("correlation").innerHTML = tabletext;

                    $(document).ready(function(){
                        $('#tablecor').DataTable();
                    });
                },
                error: function (xhr, textStatus, errorThrown) {
                }
            });
        }

        function send_to_Enrichr() {

            var popup = true;
            var description = "Genes co-expressed with "+gene;

            var form = document.createElement('form');
            form.setAttribute('method', 'post');
            form.setAttribute('action', 'https://amp.pharm.mssm.edu/Enrichr/enrich');
            if (popup){
                form.setAttribute('target', '_blank');
            }
            form.setAttribute('enctype', 'multipart/form-data');

            var listField = document.createElement('input');
            listField.setAttribute('type', 'hidden');
            listField.setAttribute('name', 'list');
            listField.setAttribute('value', genes);
            form.appendChild(listField);

            var descField = document.createElement('input');
            descField.setAttribute('type', 'hidden');
            descField.setAttribute('name', 'description');
            descField.setAttribute('value', description);
            form.appendChild(descField);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function loadDendrogram(species, gene, type){
            // main svg

            if(type == "tissue"){
                $("#tissueexpression").html("<svg id=\"dendrogramt\" width=\"1000\" height=\"1000\"></svg>");
                var svg = d3.select("#dendrogramt"),
                    width = +svg.attr("width"),
                    height = +svg.attr("height"),
                    g = svg.append("g").attr("transform", "translate(50,20)");       // move right 20px.

            }
            else{
                $("#celllineexpression").html("<svg id=\"dendrogramc\" width=\"1000\" height=\"1000\"></svg>");
                var svg = d3.select("#dendrogramc"),
                    width = +svg.attr("width"),
                    height = +svg.attr("height"),
                    g = svg.append("g").attr("transform", "translate(50,20)");       // move right 20px.

            }


            // x-scale and x-axis
            var experienceName = ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20"];
            var formatSkillPoints = function (d) {
                return experienceName[d % 19];
            }
            var xScale =  d3.scaleLinear()
                .domain([0,18])
                .range([50, 260]);

            var xAxis = d3.axisTop()
                .scale(xScale)
                .ticks(8)
                .tickFormat(formatSkillPoints);

            // Setting up a way to handle the data
            var tree = d3.cluster()                 // This D3 API method setup the Dendrogram datum position.
                .size([height-20, width - 660])    // Total width - bar chart width = Dendrogram chart width
                .separation(function separate(a, b) {
                    return a.parent == b.parent            // 2 levels tree grouping for category
                    || a.parent.parent == b.parent
                    || a.parent == b.parent.parent ? 0.4 : 0.8;
                });

            var stratify = d3.stratify()            // This D3 API method gives cvs file flat data array dimensions.
                .parentId(function(d) { return d.id.substring(0, d.id.lastIndexOf(".")); });

            function row(d) {
                return {
                    id: d.id,
                    median: +d.median,
                    q1: +d.q1,
                    q3: +d.q3,
                    min: +d.min,
                    max: +d.max,
                    color: d.color
                };
            }
            
            var link = "../search/loadExpressionTissue.php?search="+gene+"&species="+species+"&type=tissue";
            if(type == "cellline"){
                link = "../search/loadExpressionTissue.php?search="+gene+"&species="+species+"&type=cellline";
            }
            
            d3.csv(link, row, function(error, data) {
                if (error) throw error;
                
                var root = stratify(data);
                tree(root);
                
                // Draw every datum a line connecting to its parent.
                var link = g.selectAll(".link")
                    .data(root.descendants().slice(1))
                    .enter().append("path")
                    .attr("class", "link")
                    .attr("d", function(d) {
                        return "M" + d.y + "," + d.x
                            + "C" + (d.parent.y + 100) + "," + d.x
                            + " " + (d.parent.y + 100) + "," + d.parent.x
                            + " " + d.parent.y + "," + d.parent.x;
                    });
                    
                // Setup position for every datum; Applying different css classes to parents and leafs.
                var node = g.selectAll(".node")
                    .data(root.descendants())
                    .enter().append("g")
                    .attr("class", function(d) { return "node" + (d.children ? " node--internal" : " node--leaf"); })
                    .attr("transform", function(d) { return "translate(" + (d.y ) + "," + d.x + ")"; });

                // Draw every datum a small circle.
                node.append("circle")
                    .attr("r", 4);

                // Setup G for every leaf datum.
                var leafNodeG = g.selectAll(".node--leaf")
                    .append("g")
                    .attr("class", "node--leaf-g")
                    .attr("transform", "translate(" + 8 + "," + -7 + ")")
                    .attr("ry", 6);

                leafNodeG.append("rect")
                    .attr("class","shadow")
                    .style("fill", function (d) {return d.data.color;})
                    .attr("width", 2)
                    .attr("height", 10)
                    .attr("rx", 2)
                    .attr("ry", 12)
                    .attr("y",3)
                    .transition()
                    .duration(800)
                    .attr("x", function (d) {return xScale(d.data.q1) + 120;})
                    .attr("width", function (d) {return xScale(d.data.q3) - xScale(d.data.q1);});

                leafNodeG.append("line")
                    .attr("class", "line")
                    .attr("x1", 1)
                    .attr("y1", 8)
                    .attr("x2", 1)
                    .attr("y2", 8)
                    .style("opacity",1)
                    .transition()
                    .duration(800)
                    .attr("x1", function (d) {return xScale(d.data.q3) + 120;})
                    .attr("x2", function (d) {return xScale(d.data.max) + 120;});

                leafNodeG.append("line")
                    .attr("class", "line")
                    .attr("x1", 1)
                    .attr("y1", 8)
                    .attr("x2", 1)
                    .attr("y2", 8)
                    .style("opacity",1)
                    .transition()
                    .duration(800)
                    .attr("x1", function (d) {return xScale(d.data.min)+120;})
                    .attr("x2", function (d) {return xScale(d.data.q1)+120;});

                leafNodeG.append("line")
                    .attr("class", "line")
                    .attr("x1", 2)
                    .attr("y1", 3)
                    .attr("x2", 2)
                    .attr("y2", 13)
                    .style("opacity",1)
                    .transition()
                    .duration(800)
                    .attr("x1", function (d) {return xScale(d.data.min)+120;})
                    .attr("x2", function (d) {return xScale(d.data.min)+120;});

                leafNodeG.append("line")
                    .attr("class", "line")
                    .attr("x1", 2)
                    .attr("y1", 3)
                    .attr("x2", 2)
                    .attr("y2", 13)
                    .style("opacity",1)
                    .transition()
                    .duration(800)
                    .attr("x1", function (d) {return xScale(d.data.max)+120;})
                    .attr("x2", function (d) {return xScale(d.data.max)+120;});

                leafNodeG.append("line")
                    .attr("class", "line")
                    .attr("x1", 2)
                    .attr("y1", 3)
                    .attr("x2", 2)
                    .attr("y2", 13)
                    .style("opacity",1)
                    .transition()
                    .duration(800)
                    .attr("x1", function (d) {return xScale(d.data.median)+120;})
                    .attr("x2", function (d) {return xScale(d.data.median)+120;});

                leafNodeG.append("text")
                    .attr("dy", 11)
                    .attr("x", 1)
                    .style("text-anchor","right")
                    .text(function (d) {
                        return d.data.id.substring(d.data.id.lastIndexOf(".") + 1);
                    });

                // Write down text for every parent datum
                var internalNode = g.selectAll(".node--internal");
                internalNode.append("text")
                    .attr("y", -6)
                    .style("text-anchor", "middle")
                    .text(function (d) {
                        return d.data.id.substring(d.data.id.lastIndexOf(".") + 1);
                    });

                // Attach axis on top of the first leaf datum.
                var firstEndNode = g.select(".node--leaf");
                firstEndNode.insert("g")
                    .attr("class","xAxis")
                    .attr("transform", "translate(" + 124 + "," + -10 + ")")
                    .call(xAxis);

                firstEndNode.append("line")
                    .attr("x1", d=> 175)
                .attr("y1", d=> -10)
                .attr("x2", d=> 175)
                .attr("y2", d=> 1060)
                .attr("stroke", "grey")
                    .attr("stroke-width", 1);

                // tick mark for x-axis
                firstEndNode.insert("g")
                    .attr("class", "grid")
                    .attr("transform", "translate(127," + (height - 15) + ")")
                    .call(d3.axisBottom()
                        .scale(xScale)
                        .ticks(5)
                        .tickSize(-height, 0, 0)
                        .tickFormat("")
                    );

                // Emphasize the y-axis baseline.
                svg.selectAll(".grid").select("line")
                    .style("stroke-dasharray","0,1")
                    .attr("transform", "translate(127," + (height - 15) + ")")
                    .style("stroke","green");

                // The moving ball
                var ballG = svg.insert("g")
                    .attr("class","ballG")
                    .attr("transform", "translate(" + 1150 + "," + (height/2) + ")");
                ballG.insert("circle")
                    .attr("class","shadow")
                    .style("fill","steelblue")
                    .attr("r", 2);
                ballG.insert("text")
                    .style("text-anchor", "middle")
                    .attr("dy",4)
                    .text("0.0");

                // Animation functions for mouse on and off events.
                d3.selectAll(".node--leaf-g")
                    .on("mouseover", handleMouseOver)
                    .on("mouseout", handleMouseOut);

                function handleMouseOver(d) {
                    var leafG = d3.select(this);

                    leafG.select("rect")
                        .attr("stroke","#4D4D4D")
                        .attr("stroke-width","2");

                    var ballGMovement = ballG.transition()
                        .duration(100)
                        .attr("transform", "translate(" + (d.y
                            + 225) + ","
                            + (d.x + 19) + ")");

                    ballGMovement.select("circle")
                        .style("fill", d.data.color)
                        .attr("r", 18);

                    ballGMovement.select("text")
                        .delay(300)
                        .text(Number(d.data.median).toFixed(1));
                }

                function handleMouseOut() {
                    var leafG = d3.select(this);
                    leafG.select("rect")
                        .attr("stroke-width","0");
                }
                
                
                if(window.location.hash.length > 2){
                    scrollPage(window.location.hash.slice(1))
                }
            });
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

        function getGeneJSON(gene){
            var storageObj = geneinfovar;
            var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(storageObj));
            var dlAnchorElem = document.getElementById('downloadAnchorElem');
            dlAnchorElem.setAttribute("href",dataStr);
            dlAnchorElem.setAttribute("download", gene+".json");
            dlAnchorElem.click();
        }

    </script>
</head>
<body>



<?php


require 'dbconfig.php';
if(isset($_GET["gene"])){

$sql="INSERT INTO genesearchinfo (gene) VALUES (?);";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_GET["gene"]);
$stmt->execute();

$geneinfo = array();
$geneinfo["gene"] = $_GET["gene"];
$description = "No gene information available.";

$sql="SELECT description FROM gene_info WHERE gene= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $_GET["gene"]);
$stmt->execute();
$stmt->bind_result($data[0]);

while ($stmt->fetch()) {
   $description = $data[0];
}

$functiontypes = array("go_bio", "chea", "mgi", "humph", "kea", "kegg");
$functionHeader = array("Predicted biological processes (GO)", "Predicted upstream transcription factors (ChEA)", "Predicted mouse phenotypes (MGI)", "Predicted human phenotypes", "Predicted kinase interactions (KEA)", "Predicted pathways (KEGG)");

if($description != "No gene information available.") {
    echo "<div id=\"geneheader\" style=\"padding: 20px; padding-top:0px; padding-bottom: 6px; border-bottom: 2px solid darkgrey;\">";
    echo "<h2 style=\"float: left;\"><a href=\"https://amp.pharm.mssm.edu/archs4\" target=\"_blank\"><img src=\"../images/archs-42.png\" style=\"height: 40px;\"></a><img src=\"../images/dnaicon2.png\"><span id=\"genesy\">" . $_GET["gene"] . " <a id=\"downloadAnchorElem\" href=\"javascript:getGeneJSON('" . $_GET["gene"] . "')\"><img src=\"../images/json.png\" width=\"35px\"></a></span></h2>";

?>
    <form class="form-inline my-2 my-lg-0" style="padding-top: 20px; float: right;"
          action="javascript:submitGeneSearch()">
        <input class="form-control mr-sm-2" id="genesymbolsearch" type="text" placeholder="Enter gene symbol...">
        <button class="btn btn-info my-2 my-sm-0" type="button" onclick="submitGeneSearch();">Search</button>
    </form>
    
<?php

echo "<div style=\"float: right; padding: 20px; padding-bottom: 0px;\"><b>Predicted funtional terms:</b> <a href=\"javascript:scrollPage('go_bio')\">GO</a> | <a href=\"javascript:scrollPage('chea')\">ChEA</a> | <a href=\"javascript:scrollPage('mgi')\">Mouse Phenotype</a> | <a href=\"javascript:scrollPage('humph')\">Human Phenotype</a> | <a href=\"javascript:scrollPage('kea')\">KEA</a> | <a href=\"javascript:scrollPage('kegg')\">KEGG</a>";
echo "<br><b>Most similar genes based on co-expression:</b> <a href=\"javascript:scrollPage('correlation')\">Pearson correlation</a><br><b>Expression levels across tissues and cell lines:</b> <a href=\"javascript:scrollPage('tissueexpression')\">Tissue Expression</a> | <a href=\"javascript:scrollPage('celllineexpression')\">Cell Line Expression</a>";
echo "</div><br><br><br><br><br><div id=\"genedesc\"><b>Description: </b>" . $description . " <a href='https://www.ncbi.nlm.nih.gov/gene/?term=" . $_GET["gene"] . "' target=_blank>NCBI Entrez Gene</a> | <a href='http://www.genecards.org/cgi-bin/carddisp.pl?gene=" . $_GET["gene"] . "' target=_blank>GeneCards</a> | <a href='http://amp.pharm.mssm.edu/Harmonizome/gene/" . $_GET["gene"] . "' target=_blank>Harmonizome</a></div>";
echo "</div>";

?>
    <div style="padding: 10px; padding-top: 10px;" id="bound">
        <div class="panel panel-default" style="overflow:hidden;">
            <div class="panel-heading">
                <h3 class="panel-title">Functional Annotation Prediction</h3>
            </div>
            <div class="panel-body" style="padding: 10px;">
                
                <?php
                
                for ($i = 0; $i < count($functiontypes); $i++) {
                    $library = $functiontypes[$i];
                    echo "<div id=\"".$library."\"><h4>".$functionHeader[$i]."</h4></div>";
                    echo "<div id=\"wrapper\"><div id='first'>";
                    
                    echo "<table id='table".$library."' class='table table-striped table-bordered compact'>";
                    echo "<thead>";
                    echo "<tr><th>Rank</th><th>Gene Set</th><th>Z-score</th></tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    
                    
                    $sql="SELECT geneset, zscore, termmatch FROM functional_prediction WHERE listtype= ? AND gene= ? GROUP BY geneset ORDER BY zscore DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ss', $library, $_GET["gene"]);
                    $stmt->execute();
                    $stmt->bind_result($data[0], $data[1], $data[2]);
                    
                    $tempscore = array();
                    $ii = 1;
                    while ($stmt->fetch()) {
                        $tempscore[$data[0]] = floatval($data[1]);
                        if ($data[2] == 1) {
                            echo "<tr style=\"background-color: #93ffe9; font-weight: bold;\"><td>" . $ii . "</td><td>* " . $data[0] . "</td><td align='right'>" . $data[1] . "</td></tr>\n";
                        } else {
                            echo "<tr><td>" . $ii . "</td><td>" . $data[0] . "</td><td align=\"right\">" . $data[1] . "</td></tr>\n";
                        }
                        $ii = $ii + 1;
                    }
                    $geneinfo[$library] = $tempscore;
                    
                    echo "</tbody>";
                    echo "</table></div>";
                    
                    echo "<script>";
                    echo "$(document).ready(function () {";
                    echo "$('#table".$library."').DataTable();";
                    echo "});";
                    echo "</script>";
                    
                    echo "<div id=\"second\">";
                    echo "<div id=\"missingtext\"></div>";
                    echo "<img src=\"https://s3.amazonaws.com/mssm-seq-images-roc/roc_". $library ."_". $_GET["gene"] .".png\" height=\"320\" width=\"480\" onerror=\"imgErrorGenepage(this);\"/>";
                    echo "</div></div><hr>";
                }
                ?>
            </div>
        </div>
        
        <?php
        echo "<script>var geneinfovar = " . json_encode($geneinfo) . ';</script>';
        ?>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Most similar genes based on co-expression
                    <a onclick="send_to_Enrichr()" style="float: right;"> Upload to Enrichr <img src="../images/enrichr.png"></a></h3>
            </div>
            <div class="panel-body" id="correlation" style="padding: 10px;">

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Tissue Expression <a href="javascript:void(0);" onclick="generateTissue();" style="float: right;">Export image »</a></h3>
            </div>
            <div class="panel-body" id="tissueexpression" style="padding: 10px;">

            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Cell Line Expression <a href="javascript:void(0);" onclick="generateCellline();" style="float: right;">Export image »</a></h3>
            </div>
            <div class="panel-body" id="celllineexpression" style="padding: 10px;">

            </div>
        </div>
    </div>


    <script type="text/javascript">


        <?php
        echo "loadCorrelation(\"" . $_GET["gene"] . "\");";
        ?>

        <?php
        echo "loadDendrogram(\"human\", \"" . $_GET["gene"] . "\", \"tissue\");";
        ?>
    </script>

    <script type="text/javascript">
        $("#bound").css('margin-top', $("#geneheader").height() + 10);
        <?php
        echo "loadDendrogram(\"human\", \"" . $_GET["gene"] . "\", \"cellline\");";
        ?>
    </script>

    <script>
        $.getJSON("../search/getGenes.php", function (data) {
            var genes = data;
            $("#genesymbolsearch").autoComplete({
                minChars: 3,
                source: function (term, suggest) {
                    term = term.toLowerCase();
                    var choices = genes;
                    var matches = [];
                    for (i = 0; i < choices.length; i++)
                        if (~choices[i].toLowerCase().indexOf(term)) matches.push(choices[i]);
                    suggest(matches);
                }
            });
        });

    </script>


    <?php
}
else{
    echo "<img src=\"../images/archs-42.png\" style=\"height: 40px;\">";
    echo "<div id=\"missing information\">No information about this gene currently available. Only enter single gene symbols.</div>";
}
}
else{
    echo "<img src=\"../images/archs-42.png\" style=\"height: 40px;\">";
    echo "<div id=\"missing information\">No gene set.</div>";
}
?>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-6277639-31', 'auto');
    ga('send', 'pageview');

</script>

<script>
    function generateTissue(){
        saveSvgAsPng(document.getElementById("dendrogramt"), "tissueexpression.png", {scale: 4, backgroundColor: "#FFFFFF"})
    }
    
    function generateCellline(){
        saveSvgAsPng(document.getElementById("dendrogramc"), "celllineexpression.png", {scale: 4, backgroundColor: "#FFFFFF"})
    }
</script>


</body>
</html>
