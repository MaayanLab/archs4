<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://www.pureexample.com/js/lib/jquery.ui.touch-punch.min.js"></script>

<script src="scripts/dropzone.js"></script>
<script src="scripts/control.js"></script>

<link href="css/dropzone.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open Sans">

<link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32" />

<script type="text/javascript">
    $(function () {
        $("#available_list,#control_list,#treatment_list").sortable({
            connectWith: "#available_list,#control_list,#treatment_list",
            start: function (event, ui) {
                ui.item.toggleClass("highlight");
            },
            stop: function (event, ui) {
                ui.item.toggleClass("highlight");
            }
        });
        $("#available_list,#control_list,#treatment_list").disableSelection();
    });

    Dropzone.options.myAwesomeDropzone = {
        paramName: "file", // The name that will be used to transfer the file
        maxFilesize: 0.5, // MB
        accept: function(file, done) {
            if (file.name == "justinbieber.jpg") {
                done("Naha, you don't.");
            }
            else { done(); }
        }
    };

</script>

<style>

    button, input, select, textarea {
        font-family : inherit;
        font-size   : 100%;
        margin: 4px;
    }

    body {
        font-family: "Open Sans";
        font-size: 20px;
        font-style: normal;
        font-variant: normal;
        font-weight: 400;
        line-height: 20px;
    }

    #control_list{
        min-height: 250px;
        width: 360px;
    }

    #treatment_list{
        min-height: 250px;
        width: 360px;
    }
    #available_list{
        min-height: 320px;
        width: 360px;
    }

    #treatmentsamples {
        padding: 3px;
        max-width:400px;
        height: 350px;
        margin: auto;
        margin-top: 0px;
        border: 2px solid darkgrey;
        border-right: 6px solid dodgerblue;
        border-radius: 0px;
        background-color: #ffffff;
    }

    #available {
        padding: 3px;
        max-width:430px;
        height: 771px;
        margin: auto;
        border: 2px solid darkgrey;
        border-right: 6px solid #49E20E;
        border-radius: 0px;
        background-color: #ffffff;
    }

    #controlsamples {
        padding: 3px;
        max-width:400px;
        height: 350px;
        margin: auto;
        border: 2px solid darkgrey;
        border-right: 6px solid orangered;
        border-radius: 0px;
        background-color: #ffffff;
    }

    #wrapper {
        width: 1680px;
        border: 0px solid black;
        overflow: hidden; /* will contain if #first is longer than #second */
    }
    #first {
        padding: 20px;
        padding-left: 0px;
        padding-right: 40px;
        width: 400px;
        height: 660px;
        float:left; /* add this */
        border: 0px solid red;
    }
    #second {
        padding: 20px;
        padding-right: 40px;
        width: 400px;
        height: 820px;
        float:left; /* add this */
        overflow: hidden; /* if you don't want #second to wrap below #first */
    }
    #third {
        padding: 20px;
        padding-top: 0px;
        width: 500px;
        height: 840px;
        overflow: hidden; /* if you don't want #second to wrap below #first */
    }

    #drop {
        width: 1380px;
        border: 2px dashed #FF3B3F;
        background-color: white;
    }

    .list {
        background-color: #95cfe7;
        border: 1px solid #2889b1;
    }

    .items .ui-selected {
        background: red;
        color: white;
        font-weight: bold;
    }

    .items {
        list-style-type: none;
        margin-left: 4px;
        margin-top:2px;
        padding: 0;
        width: 300px;
        float: left;
    }

    .items li {
        margin: 2px;
        margin-bottom: 6px;
        padding: 12px;
        cursor: pointer;
        border-radius: 0px;
        height: 30px;
        border-radius: 3px;
    }

    .g {
        background-color: lightgreen;
    }

    .o {
        background-color: orange;
    }

    .highlight {
        border: 3px dashed #2889b1;
        font-weight: bold;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    a:link {
        text-decoration: none;
        color:darkgrey;
    }

    div#banner { 
       position: absolute; 
       top: 0; 
       left: 0; 
       background-color: #white; 
       width: 100%;
       height: 100;
       border-bottom:10px solid #00a2e5;
       box-shadow: 2px 2px 5px #888888;
       padding:16px;
       color: white;
       float: left;
     }
     div#banner-content {
        width: 1500px;
        display: flex;
        overflow: hidden;
        border:0px solid black;
     }

     #maincontent {
        width: 1480px;
        background-color: #EFEFEF;
        padding:50px;
        margin-top:140px;
        border:0px solid black;
    }
     
     div#main-content { 
       padding-top: 70px;
    }

    .effect7{
        position:relative;
        -webkit-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
        -moz-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
        box-shadow:0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
    }
    .effect7:before, .effect7:after
{
    content:"";
    position:absolute;
    z-index:-1;
    -webkit-box-shadow:0 0 20px rgba(0,0,0,0.8);
    -moz-box-shadow:0 0 20px rgba(0,0,0,0.8);
    box-shadow:0 0 20px rgba(0,0,0,0.8);
    top:0;
    bottom:0;
    left:10px;
    right:10px;
    -moz-border-radius:100px / 10px;
    border-radius:100px / 10px;
}
.effect7:after
{
    right:10px;
    left:auto;
    -webkit-transform:skew(8deg) rotate(3deg);
       -moz-transform:skew(8deg) rotate(3deg);
        -ms-transform:skew(8deg) rotate(3deg);
         -o-transform:skew(8deg) rotate(3deg);
            transform:skew(8deg) rotate(3deg);
}

</style>

<title>PSI</title>

  <div id="banner">
    <div id="banner-content">
    <div style="float:left;"><img src="images/psi_text.png"></div>
    <div style="width:520px;"></div>
    <div class="pure-menu pure-menu-horizontal" style="float:right; width:400px; margin-top:40px;">

    <a href="#" class="pure-menu-heading pure-menu-link" >WORKSPACES</a>
    <a href="#" class="pure-menu-heading pure-menu-link">SIGNATURES</a>
    <a href="#" class="pure-menu-heading pure-menu-link">PROFILE</a>
    </div>
    </div>
    
  </div>

<div id="maincontent">

<h1>Upload Sequencing Files</h1>
Drag files into upload field
<br><br>

<form method="post" action=upload.php enctype="multipart/form-data" id="drop" class="dropzone">
    <div class="fallback">
        <input name="userseq2" type="file" />
    </div>
</form>


<h1>Create Signature</h1>
Choose control and treatment samples by dragging the samples in the respective fields
<br><br>

<div id="wrapper">
    <div id="first">
        <div class="box effect7" style="border: 2px solid darkgrey; border-right: 6px solid #49E20E; border-bottom:0px solid white; margin-bottom:0px; padding:16px; background-color:#cdcdcd"><b>Available Samples</b></div>
        <div class="pure-menu pure-menu-scrollable custom-restricted" id="available">
            <ul id="available_list" class="items">
                <li class="list">SRR312311</li>
                <li class="list">SRR432167</li>
                <li class="list">SRR674921</li>
                <li class="list">SRR121354</li>
                <li class="list">SRR672311</li>
                <li class="list">SRR435879</li>
                <li class="list">SRR234792</li>
                <li class="list">SRR274893</li>
            </ul>
        </div>
    </div>

    <div id="second">

        <div class="box effect7" style="border: 1px solid darkgrey; border-right: 6px solid orangered; border-bottom:0px solid white; margin-bottom:0px; padding:16px; background-color:#cdcdcd"><b>Control Samples</b></div>


        <div class="pure-menu pure-menu-scrollable custom-restricted" id="controlsamples" ondrop="drop(event)" ondragover="allowDrop(event)">
            <ul id="control_list" class="items"></ul>
        </div>

        <div></div>
        <br>
        <div class="box effect7" style="border: 1px solid darkgrey; border-right: 6px solid dodgerblue; border-bottom:0px solid white; margin-bottom:0px; padding:16px; background-color:#cdcdcd;">
<b>Treatment Samples</b>
        </div>

        <div class="pure-menu pure-menu-scrollable custom-restricted" id="treatmentsamples" ondrop="drop(event)" ondragover="allowDrop(event)">
            <ul id="treatment_list" class="items"></ul>
        </div>

        <div></div>
    </div>

<div id="third">
    <form class="pure-form">
        <fieldset>

        <b>Signature Name</b>
        <br>
        <input type="text" name="signaturename" size="40" value="<?php echo $name;?>">
        <br>
        <br>
        <b>Choose Organism</b>
        <br>
        <input type="radio" name="organism" value="Mouse" style="height:25px; width:25px; vertical-align: middle;"> Mouse
        <input type="radio" name="organism" value="Rat" style="height:25px; width:25px; vertical-align: middle;"> Rat
        <input type="radio" name="organism" value="Human" style="height:25px; width:25px; vertical-align: middle;" checked="checked"> Human

        <br>
        <br>
        <b>Description</b>
        <br>
        <textarea name="comment" rows="8" cols="39"><?php echo $comment;?></textarea>
        <br>
        <br>
        <b>Platform</b>
        <br>
        <input type="text" name="platform" size="40" value="<?php echo $website;?>">
        <br>
        <br>
        <b>Perturbed Gene</b>
        <br>
        <input type="text" name="website" size="40" value="<?php echo $website;?>">
        <br>
        <br>
        <b>Small Molecule</b>
        <br>
        <input type="text" name="website" size="40" value="<?php echo $website;?>">
        <br>
        <br>
        <b>Cell-type/Tissue</b>
        <br>
        <input type="text" name="tissue" size="40" value="<?php echo $website;?>">
        <br>
        <br>
        <button type="submit" class="pure-button pure-button-primary">Create Signature</button>

    </fieldset>
</form>


    </div>



</div>

</div>


<div class="footer container-full" style="background-color:white; padding; border-top: 3px solid #00a2e5; padding:30px;">
    <div class="container">
        <div class="pull-left" style="float:left;">
         
                    <a href="http://icahn.mssm.edu/research/labs/maayan-laboratory" target="_blank">Ma'ayan Lab<span class="hide-on-mobile">oratory of Computational Systems Biology</span></a>
              |
                    <a href="contact">Contact Us</a>
               |
                    <a href="terms">Terms</a>
               
            </ul>
            <div id="citation">
                
            </div>
            <div>
                <h3>Funding</h3>
                
                <a href="http://lincs-dcic.org/" target="_blank">BD2K-LINCS Data Coordination and Integration Center</a>
                <br><br>
            </div>
        </div>
        <div id="share" class="pull-right hide-on-mobile" style="float:right;">
            <a href="https://www.linkedin.com/shareArticle?url=http://www.maayanlab.net/harmonizome" target="_blank">
                <img src="images/linkedin.png" height="42" width="42">
            </a>

            <a href="https://plus.google.com/share?url=http://www.maayanlab.net/harmoziome" target="_blank">
                <img src="images/google-plus.png" height="42" width="42">
            </a>

            <a href="http://www.facebook.com/sharer/sharer.php?u=http://www.maayanlab.net/harmonizome/" target="_blank">
                <img src="images/facebook.png" height="42" width="42">
            </a>

            <a href="http://twitter.com/share" target="_blank">
                <img src="images/twitter.png" height="42" width="42">
            </a>

            <br>
        </div>
    </div>
</div>

