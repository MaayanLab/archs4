
var activeSpecies = "human";
var activeMode = "sample";

var enrichmentTerms = [];
var enrichmentLibraries = [];
    
colorSets = [];
colorSets[0] = [];
colorSets[1] = [];
colorSets[2] = [];
colorSets[3] = [];

var pointGeo = new THREE.Geometry();
var startColor = ["#ffc700","#099DD7","#dd33dd","#248E84","#F2583F","#96503F"]
var colorCounter = 0

var copyStrings = [];
copyStrings[0] = [];
copyStrings[1] = [];
copyStrings[2] = [];
copyStrings[3] = [];

var colorNames = [];
colorNames[0] = [];
colorNames[1] = [];
colorNames[2] = [];
colorNames[3] = [];

var colorID = 0;
var scatterPlot = new THREE.Object3D();

var dataset = [];
var sampleID = [];

var clipboard = new Clipboard('.btn');

var mouseMode = "rotation";
var mouseDX = 0;
var mouseDY = 0;

var angle1 = 0;
var angle2 = 0;
var radius = 120;

var manualSelectCounter = 0;

var renderer = new THREE.WebGLRenderer({
    antialias: false,
    preserveDrawingBuffer: true
});

var w = 656;
var h = 656;
renderer.setSize(w, h);

renderer.setClearColor(new THREE.Color(0xffffff));
document.getElementById("center").appendChild(renderer.domElement);

var camera = new THREE.PerspectiveCamera(45, w / h, 1, 10000);

x = radius * Math.cos(angle1+0.01) * Math.sin(angle2+0.01);
y = radius * Math.sin(angle1+0.01) * Math.sin(angle2+0.01);
z = radius * Math.cos(angle2+0.01);

camera.position.y = y;
camera.position.x = x;
camera.position.z = z;


controls = new THREE.OrbitControls( camera, renderer.domElement );

var scene = new THREE.Scene();
scene.add(scatterPlot);

scatterPlot.rotation.y = 0;

function v(x, y, z) {
    return new THREE.Vector3(x, y, z);
}

$( function() {
    $( document ).tooltip();
} );

chooseSpecies(activeSpecies, activeMode);
loadEnrichmentTerms();

var saveLink = document.createElement('div');
var strDownloadMime = "image/octet-stream";
saveLink.style.top = '10px';
saveLink.style.width = '100%';
saveLink.style.background = '#FFFFFF';
saveLink.style.textAlign = 'center';
saveLink.innerHTML = '<a href="#" id="saveLink">Save Frame</a>';




//document.body.appendChild(saveLink);
//document.getElementById("saveLink").addEventListener('click', saveAsImage);

$(document).ready(function(){
    var a = sessionStorage.getItem("species");

    if(a){
        if(a == "Mouse"){
            setTimeout(function(){
                chooseSpecies(a.toLowerCase(), "sample");
            }, 1000);
        }
    }
    else{
        sessionStorage.setItem("species", "Human");
    }
    
    $("#accordian h3").click(function(){
        //slide up all the link lists
        $("#accordian ul ul").slideUp();
        //slide down the link list below the h3 clicked - only if its closed
        if(!$(this).next().is(":visible"))
        {
            $(this).next().slideDown();
        }
    });
    $("#gene_symbol_list").resizable();
    $("#gene_genepage_info").resizable();
})

this.customUniforms = 
{
    time:    { type: "f", value: 1.0 },
};

// properties that may vary from particle to particle. only accessible in vertex shaders!
//  (can pass color info to fragment shader via vColor.)
var customAttributes = 
{
    customColor:     { type: "c", value: [] },
    customFrequency: { type: 'f', value: [] },
};

// assign values to attributes, one for each vertex of the geometry
for( var v = 0; v < 100; v++ ) 
{
    customAttributes.customColor.value[ v ] = new THREE.Color( 0xffffff * Math.random() );
    customAttributes.customFrequency.value[ v ] = 5 * Math.random() + 0.5;
}

var shaderMaterial = new THREE.BufferGeometry({
    uniforms:       customUniforms,
    attributes:     customAttributes,
    vertexShader:   document.getElementById( 'vertexshader' ).textContent,
    fragmentShader: document.getElementById( 'fragmentshader' ).textContent,
    transparent: true, alphaTest: 0.5,  // if having transparency issues, try including: alphaTest: 0.5, 
    // blending: THREE.AdditiveBlending, depthTest: false,
});

function getLabel(color, text){
    var entry = document.createElement('div');
    entry.style.display = 'inline';
    entry.style.width='200px';
    var colorCode = document.createElement('div');
    
    //text2.style.zIndex = 1;    // if you still don't see the label, try uncommenting this
    colorCode.style.width = 14;
    colorCode.style.height = 14;
    colorCode.style.backgroundColor = color;
    colorCode.style.border = "1px solid grey";
    colorCode.style.float = "left";
    colorCode.style.marginRight = 8;
    colorCode.style.marginTop = 2;
    
    //text2.style.zIndex = 1;    // if you still don't see the label, try uncommenting this
    var text2 = document.createElement('div');
    text2.id = "legendLabelText"
    text2.innerHTML = text;
    text2.style.float = "left";
    text2.style.onselectstart="return false";
    entry.appendChild(colorCode);
    entry.appendChild(text2);
    
    return entry;
}


function makeUnselectable(node) {
    if (node.nodeType == 1) {
        node.setAttribute("unselectable", "on");
    }
    var child = node.firstChild;
    while (child) {
        makeUnselectable(child);
        child = child.nextSibling;
    }
}

function hexToRgb(hex) { //TODO rewrite with vector output
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function changeColor(termid, points, colo) {
    var samples = colorSets[colorID][termid];
    
    if(samples != null){
        for(i=0; i<samples.length; i++){
            var id = sampleID.indexOf(""+samples[i]);
            if(id > -1){
                points.colors[id] = new THREE.Color(colo);
            }
        }
        points.colorsNeedUpdate=true;
    }
    else{
        
    }
}

function saveAsImage() {
    var imgData, imgNode;

    try {
        var strMime = "image/png";
        imgData = renderer.domElement.toDataURL(strMime);
        
        saveFile(imgData.replace(strMime, strDownloadMime), "samplecloud.png");

    } catch (e) {
        console.log(e);
        return;
    }
}

function changeTabs(type){
    if(type == "sample"){
        $('#tabcontainer').html("<ul class=\"nav nav-tabs\"><li id=\"metatab\" role=\"presentation\" class=\"active\"><a data-toggle=\"tab\" onclick=\"toggleSearch('meta')\" href=\"#meta\">Metadata</a></li><li id=\"signaturetab\" role=\"presentation\"><a data-toggle=\"tab\" onclick=\"toggleSearch('signature')\" href=\"#signature\">Signature</a></li><li id=\"enrichmenttab\" role=\"presentation\"><a data-toggle=\"tab\" onclick=\"toggleSearch('enrichment')\" href=\"#enrichment\">Enrichment</a></li></ul><div id=\"searchpane\"></div>");
        toggleSearch("meta");
    }
    else{
        $('#tabcontainer').html("<div id=\"searchpane\"></div>");
        $('#searchpane').load('tabs/searchgenes.html',function() {
            $("#methodText").click(function(){
                $("#methodToggle").animate({ opacity: 1.0 },200).slideToggle();
            });
            $("#methodToggle").hide();
            
            var optionArr = ["KEGG pathways 2016","CHEA 2016", "KEA 2016", "GO Biological Process", "MGI Mammalian Phenotype Lvl 4"];
            
            for (var i = 0; i<enrichmentLibraries.length; i++){
                if(optionArr.includes(enrichmentLibraries[i])){
                    $('#enrichLib')
                         .append($("<option></option>")
                                    .attr("value",enrichmentLibraries[i])
                                    .text(enrichmentLibraries[i]));
                }
            }
            
            replaceEnrichmentTerms(enrichmentLibraries[0]);
            
            $('#enrichTerm').select2();
            
            $("#enrichLib").change(function () {
                replaceEnrichmentTerms($(this).val());
            });
            
            $.getJSON("search/getGenes.php", function(data){
                var genes = data;
                $("#genesymbolsearch2").autoComplete({
                    minChars: 3,
                    source: function(term, suggest){
                        term = term.toLowerCase();
                        var choices = genes;
                        var matches = [];
                        for (i=0; i<choices.length; i++)
                            if (~choices[i].toLowerCase().indexOf(term)) matches.push(choices[i]);
                        suggest(matches);
                    }
                });
            });
        });
    }
}

function changeSize(x,y){
    renderer.setSize(x, y);
}

$("#maxview").click(function(){
    moveSide();
});

$("#minview").click(function(){
    moveUp();
});

function replaceEnrichmentTerms(name){
    var terms = enrichmentTerms[name].sort(function (a, b) {
        return a.toLowerCase().localeCompare(b.toLowerCase());
    });
    $('#enrichTerm').children().remove();
    for (var i = 0; i<terms.length; i++){
        $('#enrichTerm').append($("<option></option>").text(terms[i]));
    }
}

function toggleSearch(type){
    if(type == "meta"){
        $('#searchpane').load('tabs/searchmeta.html',function() {
            $( "#menuu" ).menu();
            $( "#menuu2" ).menu();
            $("#methodText").click(function(){
                $("#methodToggle").animate({ opacity: 1.0 },200).slideToggle();
            });
            $("#methodToggle").hide();
            
            var a = sessionStorage.getItem("sent");
            if(a){
                if(a != "null"){
                    document.getElementById('sigNameInput').value = a;
                    setTimeout(function(){
                        searchMeta();
                    }, 2000);
                }
                sessionStorage.setItem("sent", null);
            }
        });
        $('#metatab').addClass("active");
        $('#enrichmenttab').removeClass("active");
        $('#signaturetab').removeClass("active");
    }
    else if(type == "enrichment"){
        $('#searchpane').load('tabs/searchenrichment.html',function() {
            $("#methodText").click(function(){
                $("#methodToggle").animate({ opacity: 1.0 },200).slideToggle();
            });
            $("#methodToggle").hide();
            
            for (var i = 0; i<enrichmentLibraries.length; i++){
                $('#enrichLib')
                     .append($("<option></option>")
                                .attr("value",enrichmentLibraries[i])
                                .text(enrichmentLibraries[i]));
            }
            
            replaceEnrichmentTerms(enrichmentLibraries[0]);
            
            $('#enrichTerm').select2();
            
            $("#enrichLib").change(function () {
                replaceEnrichmentTerms($(this).val());
            });
        });
        
        $('#metatab').removeClass("active");
        $('#enrichmenttab').addClass("active");
        $('#signaturetab').removeClass("active");
    }
    else if(type = "signature"){
        $('#searchpane').load('tabs/searchsignature.html',function() {
            $("#methodText").click(function(){
                $("#methodToggle").animate({ opacity: 1.0 },200).slideToggle();
            });
            $("#methodToggle").hide();
        });
        $('#metatab').removeClass("active");
        $('#enrichmenttab').removeClass("active");
        $('#signaturetab').addClass("active");
    }
}

function loadEnrichmentTerms(){
    $.getJSON("search/loadenrichment.php", function(data){
        enrichmentLibraries = Object.keys(data);
        enrichmentTerms = data;
    });
}

function moveUp(){
    changeSize(308,308);
    $("#projection-box").prependTo("#left");
    //$("#resultbox").prependTo("#outer_center");
    $("#legendcontainer").css("left", 142);
    $("#legendcontainer").css("top", -8);
    $("#legendcontainer").css("transform", "scale(0.6)");
    $("#projection-resize").attr("onclick", "moveSide()");
}

function moveSide(){
    $("#projection-box").prependTo("#outer_center");
    changeSize(w,h);
    //$("#resultbox").prependTo("#resultcontainer");
    $("#legendcontainer").css("left", 445);
    $("#legendcontainer").css("top", 6);
    $("#legendcontainer").css("transform", "scale(1)");
    $("#projection-resize").attr("onclick", "moveUp()");
}

function downloadScript(termid, cid) {
  var searchterm = $("#"+termid).attr('namestr');
  var element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(copyStrings[cid][termid]));
  element.setAttribute('download', searchterm+".R");
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
}

function downloadGenes(termid, cid) {
  var searchterm = $("#"+termid).attr('namestr');
  var element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(copyStrings[cid][termid]));
  element.setAttribute('download', searchterm+"_genes.txt");
  element.style.display = 'none';
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
}

function chooseSpecies(speciesname, modename){
    
    if(activeMode != modename){
        changeTabs(modename);
    }
    
    activeSpecies = speciesname;
    activeMode = modename;
    
    $("#humanSelect").css("border", "0px solid black");
    $("#mouseSelect").css("border", "0px solid black");
    $("#sampleSelect").css("border", "0px solid black");
    $("#geneSelect").css("border", "0px solid black");

    $("#"+speciesname+"Select").css("border", "3px solid black");
    $("#"+modename+"Select").css("border", "3px solid black");


    $("#calculating").show();
    
    $("#speciesinfo").fadeOut(function() {
      $(this).text(speciesname.capitalize()).fadeIn();
    });
    
    $( "#center" ).fadeOut( "slow", function() {
        //colorSets = [];
        pointGeo = new THREE.Geometry();
        startColor = ["#ffc700","#099DD7","#dd33dd","#248E84","#F2583F","#96503F"]
        
        if(speciesname == "human" && modename == "sample"){
            colorID = 0;
        }
        else if(speciesname == "mouse" && modename == "sample"){
            colorID = 1;
        }
        else if(speciesname == "human" && modename == "gene"){
            colorID = 2;
        }
        else if(speciesname == "mouse" && modename == "gene"){
            colorID = 3;
        }
        
        scatterPlot = new THREE.Object3D();
        
        //document.getElementById('sample-resultlist').innerHTML = "";

        renderer.setClearColor(new THREE.Color(0xffffff));
        document.getElementById("center").appendChild(renderer.domElement);
        
        radius = 130
        camera = new THREE.PerspectiveCamera(45, w / h, 1, 10000);
        x = radius * Math.cos(angle1+0.01) * Math.sin(angle2+0.01);
        y = radius * Math.sin(angle1+0.01) * Math.sin(angle2+0.01);
        z = radius * Math.cos(angle2+0.01);

        camera.position.y = y;
        camera.position.x = x;
        camera.position.z = z;

        
        controls = new THREE.OrbitControls( camera, renderer.domElement );


        scene = new THREE.Scene();

        scatterPlot = new THREE.Object3D();
        scene.add(scatterPlot);

        scatterPlot.rotation.y = 0;

        function v(x, y, z) {
            return new THREE.Vector3(x, y, z);
        }

        dataset = [];
        sampleID = [];

        file = modename+"_"+speciesname+"_tsne.csv";
        activeSpecies = speciesname;

        d3.csv(file, function(d) {
            
            d.forEach(function (d,i) {
                if(activeMode == "sample"){
                    dataset[i] = [ +d["x"], +d["y"], +d["z"], +d["samples"] ];
                    sampleID[i] = (d["samples"]);
                }
                else{
                    dataset[i] = [ +d["x"], +d["y"], +d["z"], +d["gene"] ];
                    sampleID[i] = (d["gene"]);
                }
            });
            
            var xExent = d3.extent(dataset, function (d) {return d[0]; }),
            yExent = d3.extent(dataset, function (d) {return d[1]; }),
            zExent = d3.extent(dataset, function (d) {return d[2]; }),
            colExent = d3.extent(dataset, function (d) {return d[3]; });

            var lineGeo = new THREE.Geometry();
            var xScale = d3.scale.linear()
                          .domain(xExent)
                          .range([-50,50]);
            var yScale = d3.scale.linear()
                          .domain(yExent)
                          .range([-50,50]);
            var zScale = d3.scale.linear()
                          .domain(zExent)
                          .range([-50,50]);
            
            var mat = new THREE.PointsMaterial({
                vertexColors: true,
                size: 1,
            });
            
            var pointCount = dataset.length;
            
            for (var i = 0; i < pointCount; i ++) {
                var x = xScale(dataset[i][0]);
                var y = yScale(dataset[i][1]);
                var z = zScale(dataset[i][2]);

                pointGeo.vertices.push(new THREE.Vector3(x, y, z));
                
                if(dataset[i][3] != 0){
                    pointGeo.colors.push(new THREE.Color(0,0,0));
                    
                }
                else{
                    pointGeo.colors.push(new THREE.Color(1,0,0));
                }
            }
            
            var points = new THREE.Points(pointGeo, mat);
            scatterPlot.add(points);
            
            renderer.render(scene, camera);
            var paused = false;
            var last = new Date().getTime();
            var down = false;
            var sx = 0,
                sy = 0;
            
            cent = document.getElementById('center');
            
            cent.onmousedown = function(ev) {
                down = true;
                sx = ev.clientX;
                sy = ev.clientY;
                mouseDX = ev.offsetX;
                mouseDY = ev.offsetY;
                
                if(mouseMode == "select"){
                    var sel = $('<div class="clickMarkers" style="pointer-events:none; position: absolute; z-index: 100; left: ' + ev.offsetX + 'px; top: ' + ev.offsetY +'px"></div>')
                    $("#center").append(sel);
                }
            };
            cent.onmouseup = function(ev) {
                down = false;
                if(mouseMode == "select"){
                    changeMouseMode();
                    $(".clickMarkers").fadeOut(200, function(){
                        $(this).remove();
                    });
                    var selectedCubes = findCubesByVertices({x: ev.offsetX, y: ev.offsetY});
                    selectHand(selectedCubes);
                }
            };
            cent.onmousemove = function(ev) {
                if (down) {
                    if(mouseMode == "select"){
                        var pos = {};
                        pos.x = ev.offsetX - mouseDX;
                        pos.y = ev.offsetY - mouseDY;
                        var marquee = $(".clickMarkers");
                        if (pos.x < 0 && pos.y < 0) {
                            marquee.css({left: ev.offsetX + 'px', width: -pos.x + 'px', top: ev.offsetY + 'px', height: -pos.y + 'px'});
                        } else if ( pos.x >= 0 && pos.y <= 0) {
                            marquee.css({left: mouseDX + 'px',width: pos.x + 'px', top: ev.offsetY, height: -pos.y + 'px'});
                        } else if (pos.x >= 0 && pos.y >= 0) {
                            marquee.css({left: mouseDX + 'px', width: pos.x + 'px', height: pos.y + 'px', top: mouseDY + 'px'});
                        } else if (pos.x < 0 && pos.y >= 0) {
                            marquee.css({left: ev.offsetX + 'px', width: -pos.x + 'px', height: pos.y + 'px', top: mouseDY + 'px'});
                        }
                    }
                    else{

                    }
                }
            }
            cent.onmousewheel = function( event ) {
                event.preventDefault();
                event.stopPropagation();
                
                radius += event.wheelDeltaY * 0.01;
                
                if(radius < 60){
                    radius = 60;
                }
                
                x = radius * Math.cos(angle1) * Math.sin(angle2);
                y = radius * Math.sin(angle1) * Math.sin(angle2);
                z = radius * Math.cos(angle2);
                
                camera.position.y = y;
                camera.position.x = x;
                camera.position.z = z;
                
            }
            var animating = false;
            cent.ondblclick = function() {
                animating = !animating;
            };
            
            function animate(t) {
                if (!paused) {
                    last = t;
                    if (animating) {
                        var v = pointGeo.vertices;
                        for (var i = 0; i < v.length; i++) {
                            var u = v[i];
                            u.angle += u.speed * 0.01;
                            u.x = Math.cos(u.angle) * u.radius;
                            u.z = Math.sin(u.angle) * u.radius;
                        }
                        pointGeo.__dirtyVertices = true;
                    }
                    renderer.clear();
                    camera.lookAt(scene.position);
                    renderer.render(scene, camera);
                }
                window.requestAnimationFrame(animate, renderer.domElement);
            };
            animate(new Date().getTime());
            onmessage = function(ev) {
                paused = (ev.data == 'pause');
            };
            
            var termids = Object.keys(colorSets[colorID]);
            for(var k=0; k<termids.length; k++){
                
                changeColor(termids[k], pointGeo, $("#"+termids[k]).spectrum("get").toHexString());
            }
            addLegend();
            
            $("#calculating").hide();
        });

        $("#center").fadeIn("slow", null);
    });
}

function findCubesByVertices(location){
  var currentMouse = {},
      mouseInitialDown = {},
      units,
      bounds,
      inside = false,
      selectedUnits = [],
      dupeCheck = {};

  currentMouse.x = location.x;
  currentMouse.y = location.y;

  mousedowncoords = {x: mouseDX,y: mouseDY};

  mouseInitialDown.x = (mouseDX - currentMouse.x);
  mouseInitialDown.y = (mouseDY - currentMouse.y);

  bounds = findBounds(currentMouse, mousedowncoords);
  
  var units = pointGeo.vertices;
  
  
  var projScreenMat = new THREE.Matrix4();
  projScreenMat.multiplyMatrices( camera.projectionMatrix, camera.matrixWorldInverse );
  
  for(var i = 1; i < units.length; i++) {
    var u = toScreenXY(units[i], projScreenMat);
    inside = withinBounds(u, bounds);
    
    if(inside){
      selectedUnits.push(sampleID[i]);
    }
  }
  
  pointGeo.colorsNeedUpdate=true;
  return selectedUnits;

}

// takes the mouse up and mouse down positions and calculates an origin
// and delta for the square.
// this is compared to the unprojected XY centroids of the cubes.
function findBounds (pos1, pos2) {
    // calculating the origin and vector.
    var origin = {},
        delta = {};

    if (pos1.y < pos2.y) {
        origin.y = pos1.y;
        delta.y = pos2.y - pos1.y;
    } else {
        origin.y = pos2.y;
        delta.y = pos1.y - pos2.y;
    }

    if(pos1.x < pos2.x) {
        origin.x = pos1.x;
        delta.x = pos2.x - pos1.x;
    } else {
        origin.x = pos2.x;
        delta.x = pos1.x - pos2.x;
    }
    return ({origin: origin, delta: delta});
}

function withinBounds(pos, bounds) {

    var ox = bounds.origin.x,
        dx = bounds.origin.x + bounds.delta.x,
        oy = bounds.origin.y,
        dy = bounds.origin.y + bounds.delta.y;

    if((pos.x >= ox) && (pos.x <= dx)) {
        if((pos.y >= oy) && (pos.y <= dy)) {
            return true;
        }
    }
    return false;
}

function toScreenXY (position, projScreenMat) {
  var pos = position.clone();

  pos.applyMatrix4(projScreenMat);

  return { x: ( pos.x + 1 ) * w / 2,
       y: ( - pos.y + 1) * h / 2};
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

function removeRow(termid){
    
    var index = -1;
    var remMode = 0;
    
    for(var i=0; i<colorNames.length; i++){
        
        var te = colorNames[i];
        for(var k=0; k<te.length; k++){
            
            if(termid == te[k]){
                
                remMode = i;
                index = colorNames[i].indexOf(termid);
                
                $("#tr-"+termid).fadeTo(400, 0, function () { 
                    $(this).remove();
                });
                
                $("#"+termid+"-result").remove();
                
                if(remMode == colorID){
                    changeColor(termid, pointGeo, "#000000");
                }
                
                $('[name="'+colorNames[remMode][index]+'"]').remove();
                
                if(colorNames[remMode].length > 0){
                    colorNames[remMode].splice(index, 1);
                    delete colorSets[remMode][termid];
                    colorSets[remMode].splice(index, 1);
                }
                addLegend();
            }
        }
    }
}

var saveFile = function (strData, filename) {
    var link = document.createElement('a');
    if (typeof link.download === 'string') {
        document.body.appendChild(link); //Firefox requires the link to be in the body
        link.download = filename;
        link.href = strData;
        link.click();
        document.body.removeChild(link); //remove the link when done
    } else {
        location.replace(uri);
    }
}

function hashCode(s){
  s = s+activeSpecies+activeMode;
  return "h"+s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);
}

function addLegend(){

    legend = document.getElementById("legend");
    var csets =document.getElementsByTagName("input");

    while (legend.hasChildNodes()) {
        legend.removeChild(legend.lastChild);
    }

    for (var j = 0; j < colorNames[colorID].length; j++){
        legend.appendChild(getLabel($("#"+colorNames[colorID][j]).spectrum("get").toHexString(), $("#"+colorNames[colorID][j]).attr('namestr')));
        legend.appendChild(document.createElement('br'));
    }
}

function changeMouseMode(){
    
    if(mouseMode == "select"){
        controls.enabled = true;
        mouseMode = "rotation";
        $("#projection-select").css('color', 'black');
        $(".projection-head").html('Rotation');
    }
    else{
        mouseMode = "select";
        controls.enabled = false;
        $("#projection-select").css('color', 'green');
        $(".projection-head").html('Select');
    }
}

function selectHand(samples){
    
    manualSelectCounter = manualSelectCounter+1;
    var  searchterm = "Manual Selection "+manualSelectCounter;
    var termid = hashCode(searchterm);
    
    colorSets[colorID][termid] = samples;
    
    if(activeMode == "sample"){
        var samp = samples.map(function (i){
            return 'GSM' + i;
        })
        getCode("buildExpressionMatrix.r", samp, searchterm, termid);

        var str = "<tr id=\"tr-"+termid+"\"><td style=\"padding: 0px;\"><div id=\""+termid+"-result\" style=\"padding:0px;\"><input type=\"text\" id=\""+termid+"\"></div></td><td>"+searchterm+"</td><td>"+(activeSpecies.charAt(0).toUpperCase() + activeSpecies.slice(1))+"</td><td>"+samples.length+"</td><td>NA</td><td style=\"font-size: 1.4em;\"><i class=\"glyphicon glyphicon-download-alt\" style=\"cursor:pointer\" onclick=\"downloadScript('"+termid+"','"+colorID+"');\"></i></td><td style=\"font-size: 1.4em;\"><div id=\"TrashButton\" class=\"glyphicon glyphicon-remove\" style=\"cursor:pointer\" onclick=\"removeRow('"+termid+"');\"></div></td></tr>";
    
        $('#sample_table tr:last').after(str);
    }
    else{
        var str = "<tr id=\"tr-"+termid+"\"><td style=\"padding: 0px;\"><div id=\""+termid+"-result\" style=\"padding:0px;\"><input type=\"text\" id=\""+termid+"\"></div></td><td>"+searchterm+"</td><td>"+(activeSpecies.charAt(0).toUpperCase() + activeSpecies.slice(1))+"</td><td><a style=\"padding:0px;\" onclick=\"openPopup('"+termid+"', '"+colorID+"')\" href=\"#\">"+samples.length+"</a></td><td><a onclick=\"forwardGenesEnrichr('"+termid+"','"+searchterm+"','"+colorID+"')\"><img src=\"images/enrichr.png\"></a></td><td style=\"font-size: 1.4em;\"><i class=\"glyphicon glyphicon-download-alt\" style=\"cursor:pointer\" onclick=\"downloadGenes('"+termid+"', '"+colorID+"');\"></i></td><td style=\"font-size: 1.4em;\"><div id=\"TrashButton\" class=\"glyphicon glyphicon-remove\" style=\"cursor:pointer\" onclick=\"removeRow('"+termid+"');\"></div></td></tr>";
        copyStrings[colorID][termid] = samples.join("\n");
        
        $('#gene_table tr:last').after(str);
    }
    
    colorNames[colorID].push(termid); 
    
    $("#"+termid).attr("namestr", searchterm);

    $("#"+termid).on("change", function() {
        changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
        addLegend();
    }); 
    
    $("#"+termid).spectrum({
        color: startColor[colorCounter % startColor.length]
    });
    
    colorCounter = colorCounter + 1;
    addLegend();
    changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
    
    $('html, body').animate({
        scrollTop: $("#resultbox").offset().top
    }, 1000);
}

function searchSamples(searchterm, termid){
    
    $.getJSON("search/getSampleMatch.php?search="+searchterm+"&species="+activeSpecies, function(data){
        var samples = data[1];
        colorSets[colorID][termid] = samples;

        var samp = samples.map(function (i){
            return 'GSM' + i;
        })
        
        getCode("buildExpressionMatrix.r", samp, searchterm, termid);
        
        var series = data[2];
        var useries = Array.from(new Set(series))
        
        $("#calculating").hide();
        
        addSampleResults(termid, searchterm, samples, useries);
        changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
    });
}

function searchGenes(){
    
    var searchterm =$("#enrichTerm option:selected").text();
    termid = hashCode(searchterm);
    if(!(termid in colorSets[colorID])){
        $("#calculating").show();
        $.getJSON("search/searchEnrichmentGenes.php?search="+searchterm, function(data){
            
            var genes = data[1];
            colorSets[colorID][termid] = genes;
            copyStrings[colorID][termid] = genes.join("\n");
            
            var str = "<tr id=\"tr-"+termid+"\"><td style=\"padding: 0px;\"><div id=\""+termid+"-result\" style=\"padding:0px;\"><input type=\"text\" id=\""+termid+"\"></div></td><td>"+searchterm+"</td><td>"+(activeSpecies.charAt(0).toUpperCase() + activeSpecies.slice(1))+"</td><td><a style=\"padding:0px;\" onclick=\"openPopup('"+termid+"', '"+colorID+"')\" href=\"#\">"+genes.length+"</a></td><td><a onclick=\"forwardGenesEnrichr('"+termid+"','"+searchterm+"','"+colorID+"')\"><img src=\"images/enrichr.png\"></a></td><td style=\"font-size: 1.4em;\"><i class=\"glyphicon glyphicon-download-alt\" style=\"cursor:pointer\" onclick=\"downloadGenes('"+termid+"', '"+colorID+"');\"></i></td><td style=\"font-size: 1.4em;\"><div id=\"TrashButton\" class=\"glyphicon glyphicon-remove\" style=\"cursor:pointer\" onclick=\"removeRow('"+termid+"');\"></div></td></tr>";
            $('#gene_table tr:last').after(str);
            
            colorNames[colorID].push(termid);
            
            $("#"+termid).attr("namestr", searchterm);
            
            $("#"+termid).on("change", function() {
                changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
                addLegend();
            }); 
            
            $("#"+termid).spectrum({
                color: startColor[colorCounter % startColor.length]
            });
            
            colorCounter = colorCounter + 1;
            addLegend();
            changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
            
            $('html, body').animate({
                scrollTop: $("#resultbox").offset().top
            }, 1000);
            
            $("#calculating").hide();
        });
    }
    else{
        alert("already exists");
    }
}

function addSampleResults(termid, searchterm, samples, useries){
    
    if(useries.length == 0){
        seriescount = "NA";
    }
    else{
        seriescount = useries.length;
    }
    
    var str = "<tr id=\"tr-"+termid+"\"><td style=\"padding: 0px;\"><div id=\""+termid+"-result\" style=\"padding:0px;\"><input type=\"text\" id=\""+termid+"\"></div></td><td>"+searchterm+"</td><td>"+(activeSpecies.charAt(0).toUpperCase() + activeSpecies.slice(1))+"</td><td>"+samples.length+"</td><td>"+seriescount+"</td><td style=\"font-size: 1.4em;\"><i class=\"glyphicon glyphicon-download-alt\" style=\"cursor:pointer\" onclick=\"downloadScript('"+termid+"', '"+colorID+"');\"></i></td><td style=\"font-size: 1.4em;\"><div id=\"TrashButton\" class=\"glyphicon glyphicon-remove\" style=\"cursor:pointer\" onclick=\"removeRow('"+termid+"');\"></div></td></tr>";
    $('#sample_table tr:last').after(str);

    colorNames[colorID].push(termid); 
    
    $("#"+termid).attr("namestr", searchterm);

    $("#"+termid).on("change", function() {
        changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
        addLegend();
    }); 
    
    $("#"+termid).spectrum({
        color: startColor[colorCounter % startColor.length]
    });
    
    colorCounter = colorCounter + 1;
    addLegend();
    
    $('html, body').animate({
        scrollTop: $("#resultbox").offset().top
    }, 1000);
}

function getCode(file, samples, searchterm, termid){
    
    $.ajax({
        url : file,
        dataType: "text",
        success : function (data) {
            var allText = data;
            
            var joincounter = 1;
            var newString = "\"";
            for(var i=0; i < samples.length; i++){
                
                if(joincounter > 30){
                    joincounter = 1;
                    newString += samples[i]+"\",\n\"";
                }
                else{
                    newString += samples[i]+"\",\"";
                }
                joincounter++;
            }
            newString += "\"";
            
            allText = allText.replace("insert_samples", newString);
            allText = allText.replace("searchterm", searchterm);
            allText = allText.replace(/selected_species/g, activeSpecies);
            copyStrings[colorID][termid] = allText;
        }
    });
}

function forwardGenesEnrichr(termid, searchterm, cid){
    send_to_Enrichr(copyStrings[cid][termid], searchterm, true);
}

function send_to_Enrichr(genes, description, popup) {
    
  if (typeof description == 'undefined')
    description = "Gene list from ARCHS4";
  if (typeof popup == 'undefined')
    popup = false;
  if (typeof genes == 'undefined')
    alert('No genes defined.');

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


function remove_search(termid){
    removeRow(termid);
}

function search_similar(searchterm, direction, termid){
    
    var jsonData = {};
    var arrayUpGenes = $('#upGenes').val().split('\n');
    var arrayDownGenes = $('#dnGenes').val().split('\n');
    
    jsonData["type"] = "geneset";
    jsonData["direction"] = direction;
    jsonData["species"] = activeSpecies;
    jsonData["signatureName"] = searchterm;
    jsonData["upgenes"] = arrayUpGenes;
    jsonData["downgenes"] = arrayDownGenes;
    
    $.ajax({
        type: "POST",
        url: "http://amp.pharm.mssm.edu/custom/rooky",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        data: JSON.stringify(jsonData),
        success: function(jdata) {
            var samples = jdata;
            
            colorSets[colorID][termid] = samples['samples'];
            
            var samp =  samples['samples'].map(function (i){
                return 'GSM' + i;
            })
            
            getCode("buildExpressionMatrix.r",samp, searchterm, termid);
            
            addSampleResults(termid, searchterm, samples['samples'], []);
            changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
            
            $("#calculating").hide();
        },
        error: function (xhr, textStatus, errorThrown) {
            $("#sample-resultlist").html(xhr.responseText);
        }
    });
}

function openPopup(termid, cid){
    var genestr = copyStrings[cid][termid];
    var genelist = genestr.split("\n");
    
    var linkstr = "";
    for(var i=0; i<genelist.length; i++){
        //linkstr += "<a onclick=\"openGenepage2('"+genelist[i]+"')\" href=\"#\">"+genelist[i]+"</a> | ";
        linkstr += "<a href=\"search/genepage.php?search=go&gene="+genelist[i]+"\" target=\"_blank\">"+genelist[i]+"</a> | ";
    }
    linkstr = linkstr.substring(0, linkstr.length - 3);
    
    var searchterm = $("#"+termid).attr('namestr');
    var genelist = "<h2><img src=\"images/dnaicon2.png\">"+searchterm+" ("+genelist.length+")</h2><hr><div style=\"overflow:scroll; height:400px;\"><p>"+linkstr+"</p></div>";
    genelist += "<p><a data-popup-close=\"popup-1\" href=\"#\">Close</a></p><a class=\"popup-close\" data-popup-close=\"popup-1\" href=\"#\">x</a></p>";
    
    $("#gene_symbol_list").html(genelist);
    
    
    $('[data-popup-close]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-close');
        $('[data-popup="popup_genelist"]').fadeOut(350);
 
        e.preventDefault();
    });
    $('[data-popup="popup_genelist"]').fadeIn(350);
}

function submitGeneSearch(){
    var geneid = $("#genesymbolsearch").val().toUpperCase();
    //openGenepage2(geneid);
    if(geneid.length > 1){
        window.open("search/genepage.php?search=go&gene="+geneid,'_blank');
    }
}

function submitGeneSearch2(){
    var geneid = $("#genesymbolsearch2").val().toUpperCase();
    
    if(geneid.length > 1){
        //openGenepage2(geneid);
        window.open("search/genepage.php?search=go&gene="+geneid,'_blank');
    }
}

function openGenepage2(geneid, type){
    if(type == null){
        type = "go";
    }
    $('[data-popup="popup_genelist"]').fadeOut(350);
    
    $('#gene_genepage_info').load("search/getFunctionalInfo.php?search="+type+"&gene="+geneid, function() {
        $('[data-popup="popup_genepage"]').fadeIn();
        
        $('[data-popup-close]').on('click', function(e){
            var targeted_popup_class = jQuery(this).attr('data-popup-close');
            $('[data-popup="popup_genepage"]').fadeOut(300);
            e.preventDefault();
        });
    });
}

function imgError(obj){
    obj.parentNode.removeChild(obj);
    $("#missingtext").html("Not enough gene annotations available.");
}

function searchEnrichment(){
    
    var searchterm =$("#enrichTerm option:selected").text();
    var direction = $("#enrichDir option:selected").text();
    termid = hashCode(searchterm+"_"+direction);
    $("#calculating").show();
    if(!(termid in colorSets[colorID])){
        
        $.getJSON("search/searchEnrichmentSamples.php?search="+searchterm+"&species="+activeSpecies+"&direction="+direction, function(data){
            var samples = data;
            colorSets[colorID][termid] = samples[1];
            
            var samp = samples[1].map(function (i){
                return 'GSM' + i;
            })
            
            getCode("buildExpressionMatrix.r", samp, searchterm+"_"+direction, termid);
            
            addSampleResults(termid, searchterm+"_"+direction, samples[1], []);
            changeColor(termid, pointGeo, $("#"+termid).spectrum("get").toHexString());
            
            $("#calculating").hide();
        });
    }
    else{
        alert("search already exists");
    }
}

function get_series(searchterm, termid){
    $.getJSON("search/getSeriesMatch.php?search="+searchterm+"&species="+activeSpecies, function(data){
        var series = data;
        document.getElementById(termid+"-seriescounts").innerHTML = "Series: "+series[1].length;
    });
}

function loadDendrogram(species, gene, type){
    // main svg
    $("#anchorlinks").fadeOut(300);
    $("#header_bio").html("");
    
    $("#gene_page_content").html("<svg id=\"dendrogram\" width=\"1000\" height=\"1000\"></svg>");
    
    var svg = d3.select("#dendrogram"),
        width = +svg.attr("width"),
        height = +svg.attr("height"),
        g = svg.append("g").attr("transform", "translate(50,20)");       // move right 20px.

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

    var link = "search/loadExpressionTissue.php?search="+gene+"&species="+species+"&type=tissue";
    if(type == "cellline"){
        link = "search/loadExpressionTissue.php?search="+gene+"&species="+species+"&type=cellline";
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
    });
}



