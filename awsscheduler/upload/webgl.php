<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Alexander Lachmann">
    <title>ARCHS</title>
    
    <link rel="icon" href="images/archs-icon2.png?v=2" type="image/png">

    <script src="scripts/three.min.js"></script>
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <script src="scripts/jquery-3.1.1.min.js"></script>
    <script src="scripts/spectrum.js"></script>
    <script src="scripts/d3.layout.cloud.js"></script>
    <script src="scripts/tags.js"></script>
    <script src="scripts/prettify.js"></script>
    <script src="scripts/clipboard.min.js"></script>
    
    <script type="text/javascript" src="scripts/word-cloud.js"></script>
    <!-- <script type="text/javascript" src="http://www.json.org/json2.js"></script> -->
    <link rel="stylesheet" type="text/css" href="css/spectrum.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="css/jquery-ui.css">

    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/footer.css">
    <link rel="stylesheet" type="text/css" href="css/desert.css">
    
    <style type="text/css">
    body {
        margin: 0px;
        padding: 0px;
        font-family: 'Open Sans', sans-serif;
    }
    #container {
        width:960px;
        height:500px;
    }
    
    </style>

</head>

<body>


<div id="nav">
    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" title="ARCHS: All RNA-seq and Chip-seq Seqeuncing" href="">
                    <span>
                        <img src="images/ARCHS-04.png">
                    </span>
                    </a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1" style="color:white;">
                <ul class="nav navbar-nav">
                    <li id="similarity"><a href="#similarity">Search</a></li>
                    <li id="browse" class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Visualize<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#bubble">Bubble chart</a></li>
                            <li><a href="#clusters">Clustergram</a></li>
                        </ul>
                    </li>
                    <li id="downloads"><a href="#downloads">Download</a></li>
                    <li id="help"><a href="#help">API</a></li>
                    <li id="contribute"><a href="#contribute">Contribute</a></li>
                </ul>
                
                <ul class="nav navbar-nav navbar-right">
                   <li id="browse" class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Legend colors<span class="caret"></span></a>
                        <ul class="dropdown-menu" id="colorlist" role="menu">
                            
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>
</div>


<div class="container" style="min-width: 1700px; padding-top: 0px">
            <div class="row">

<div id="left" class="col-sm-3 left">
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Species</h3>
        </div>
        <div class="panel-body" style="padding: 10px;">
            <button direction="similar" id="humanSelect" class="btn btn-info" style="width: 80px;"><img src="images/human_foot_small.png" style="padding-bottom: 5px;"/><br>Human</button>
            &nbsp;
            <button direction="similar" id="mouseSelect" class="btn btn-info" style="width: 80px;"><img src="images/mouse_foot_small.png" style="padding-bottom: 5px;"/><br>Mouse</button>
            
        </div>
    </div>
    
   <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Search</h3>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
              <li role="presentation" class="active"><a href="#">Metadata</a></li>
              <li role="presentation"><a onclick="changeSize()" href="#">Signature Similarity</a></li>
              <li role="presentation"><a href="#">Enrichment</a></li>
            </ul>
            <div>
                <h3>Metadata Signature Search</h3>
                <p>Examples: <a class="exampleTerm">Blood</a>, <a class="exampleTerm">Macrophage</a>, <a class="exampleTerm">Brain</a></p>
                <div class="input-group">
                    <span class="twitter-typeahead" style="position: relative; display: inline-block;">
                    <input type="text" class="form-control tt-hint" readonly="" autocomplete="off" spellcheck="false" tabindex="-1" style="position: absolute; top: 0px; left: 0px; border-color: transparent; box-shadow: none; opacity: 1; background: none 0% 0% / auto repeat scroll padding-box border-box rgb(255, 255, 255);" dir="ltr">
                    <input id="sigNameInput" type="text" class="form-control tt-input" placeholder="Search for signatures..." autocomplete="off" spellcheck="false" dir="auto" style="width: 292px; position: relative; vertical-align: top; background-color: transparent;"><pre aria-hidden="true" style="position: absolute; visibility: hidden; white-space: pre; font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-size: 13px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; word-spacing: 0px; letter-spacing: 0px; text-indent: 0px; text-rendering: auto; text-transform: none;"></pre>
                    <div class="tt-menu" style="position: absolute; top: 100%; left: 0px; z-index: 100; display: none;">
                    <div class="tt-dataset tt-dataset-genes"></div>
                    <div class="tt-dataset tt-dataset-dzs"></div>
                    <div class="tt-dataset tt-dataset-drugs"></div>
                    </div></span>
                    <span class="input-group-btn">
                        <button id="sigNameBtn" class="btn btn-info" type="button">Search</button>
                    </span>
                </div>
            </div>
            <hr>
            <div>
                <h3>Signature Search</h3>
                <p class="help-block">Search signatures by up and down gene sets</p>
                <p>
                    <a id="geneSearchEgBtn">Try an example</a>
                </p>
                <form>
                <div>
                    <label for="sigName" class="control-label">Signature name</label>
                        <input id="sigName" type="text" class="form-control tt-input" placeholder="Signature name..." autocomplete="off" spellcheck="false" dir="auto" style="position: relative; vertical-align: top; background-color: transparent;">
                    </div>
                    <div>
                        <label for="upGenes" class="control-label">Up genes</label>
                        <textarea class="form-control" id="upGenes" rows="10" placeholder="Up genes"></textarea>
                    </div>
                    <div id="dnGenes-wrapper">
                        <label for="dnGenes" class="control-label">Down genes</label>
                        <textarea class="form-control" id="dnGenes" rows="10" placeholder="Down genes"></textarea>
                    </div>
                   
                </form>
            </div>
            <div>
                
                    <button direction="similar" class="btn btn-info btn-sm geneSearchBtn">Search similar signatures</button>
                    <button direction="opposite" class="btn btn-info btn-sm geneSearchBtn pull-right">Search opposite signatures</button>
                
            </div>
        </div>
    </div>
</div>



<div id="outer_center" class="col-sm-8" style="min-width: 1120px;">
    <div class="panel panel-default" id="projection-box">
    <div class="panel-heading" style="height: 40px;">
        <h3 class="panel-title projection-head">Subspace Projection</h3>
        <span class="glyphicon glyphicon-resize-small projection-shrink" id="projection-resize" onclick="moveUp();"></span>
            
        </div>
            <div class="panel-body" style="position: relative; padding:0px;">
                <div class="legendcontainer" id="legendcontainer">
                    
                    <div style="width: 180px;">
                        <div style="width: 100px; float:left;">
                        <h3 class="no-margin" id="speciesinfo">Human</h3>
                        </div>
                        <div id="calculating" style="float:right; padding-top: 14px;"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span></div>
                    </div>
                     <div class="legend" id="legend">
                     </div>
                     
                </div>
                <div id="center">
            </div>
            
        </div>
    </div>
</div>
</div>
</div>

<div class="container" style="min-width: 1000px; padding-top: 0px" id="resultcontainer">
    <div class="row" id="resultbox">
        <div id="results" class="ol-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Search Result</h3>
                </div>
                <div class="panel-body" style="padding: 10px;">
                   <div id="resultlist"> </div>
                </div>
            </div>
        </div>
    </div>
</div>


</div>
</div>
</div>


<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<footer>
    <div class="footer" id="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-8  col-md-8 col-sm-8 col-xs-12">
                    <h3> Affiliations</h3>
                     <ul>
                        <li><a href="http://icahn.mssm.edu/research/labs/maayan-laboratory" target="_blank">The Ma'ayan Lab</a></li>
                        <li><a href="http://www.lincs-dcic.org/" target="_blank">BD2K-LINCS Data Coordination and Integration Center (DCIC)</a></li>
                        <li><a href="http://www.lincsproject.org/">NIH LINCS program</a></li>
                        <li><a href="http://bd2k.nih.gov/" target="_blank">NIH Big Data to Knowledge (BD2K)</a></li>
                        <li><a href="https://commonfund.nih.gov/idg/index" target="_blank">NIH Illuminating the Druggable Genome (IDG) Program</a></li>
                        <li><a href="http://icahn.mssm.edu/" target="_blank">Icahn School of Medicine at Mount Sinai</a></li>
                    </ul>
                </div>
               
                <div class="col-lg-4  col-md-4 col-sm-4 col-xs-12">
                    <h3> Share and Contact </h3>
                    <ul>
                        <li>
                            <div class="input-append newsletter-box text-center">
                                <input type="text" class="full text-center" placeholder="Email">
                                <button class="btn  bg-gray" type="button"> Send us feedback <i class="fa fa-long-arrow-right"> </i> </button>
                            </div>
                        </li>
                    </ul>
                    <ul class="social">
                        <li> <a href="#"> <i class="fa fa-facebook">   </i> </a> </li>
                        <li> <a href="#"> <i class="fa fa-twitter">   </i> </a> </li>
                        <li> <a href="#"> <i class="fa fa-google-plus">   </i> </a> </li>
                        <li> <a href="#"> <i class="fa fa-pinterest">   </i> </a> </li>
                        <li> <a href="#"> <i class="fa fa-youtube">   </i> </a> </li>
                    </ul>
                </div>
            </div>
            <!--/.row--> 
        </div>
        <!--/.container--> 
    </div>
    <!--/.footer-->
    
    <div class="footer-bottom">
        <div class="container">
            <p class="pull-left"> Copyright © Ma'ayan Lab. All right reserved. </p>
        </div>
    </div>
    <!--/.footer-bottom--> 
</footer>


<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<script src="scripts/logic.js"></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-589df90e59994e28"></script> 
<script src="scripts/search.js"></script>
</body>




















