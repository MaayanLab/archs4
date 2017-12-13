
var activeSpecies = "human";
    
colorSets = [];
var pointGeo = new THREE.Geometry();
var startColor = ["#ffc700","#099DD7","#dd33dd","#248E84","#F2583F","#96503F"]
var colorCounter = 0

var copyStrings = [];

var colorNames = [];
var scatterPlot = new THREE.Object3D();

var dataset = [];
var sampleID = [];

var clipboard = new Clipboard('.btn');

var renderer = new THREE.WebGLRenderer({
    antialias: false,
    preserveDrawingBuffer: true
});

var w = 1100;
var h = 1100;
renderer.setSize(w, h);

renderer.setClearColor(new THREE.Color(0xffffff));
document.getElementById("center").appendChild(renderer.domElement);

var camera = new THREE.PerspectiveCamera(45, w / h, 1, 10000);
camera.position.z = 137;
camera.position.x = -60;
camera.position.y = 100;

var scene = new THREE.Scene();
scene.add(scatterPlot);

scatterPlot.rotation.y = 0;

function v(x, y, z) {
    return new THREE.Vector3(x, y, z);
}

chooseSpecies("human");

var saveLink = document.createElement('div');
var strDownloadMime = "image/octet-stream";
saveLink.style.top = '10px';
saveLink.style.width = '100%';
saveLink.style.background = '#FFFFFF';
saveLink.style.textAlign = 'center';
saveLink.innerHTML = '<a href="#" id="saveLink">Save Frame</a>';

//document.body.appendChild(saveLink);
//document.getElementById("saveLink").addEventListener('click', saveAsImage);

setInterval(function() {
    addLegend();
}, 1000);


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
    colorCode.style.marginRight = 12;
    colorCode.style.marginTop = 2;
    
    //text2.style.zIndex = 1;    // if you still don't see the label, try uncommenting this
    var text2 = document.createElement('div');
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

function changeColor(samples, points, colo) { //TODO rewrite with vector output
    for(i=0; i<samples.length; i++){
        var id = sampleID.indexOf(samples[i]);
        if(id > -1){
            points.colors[id] = new THREE.Color(colo);
        }
    }
    points.colorsNeedUpdate=true;
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

function changeSize(x,y){
    renderer.setSize(x, y);
}

function moveUp(){
    changeSize(390,390);
    $("#projection-box").prependTo("#left");
    $("#resultbox").prependTo("#outer_center");
    $("#legendcontainer").css("left", 230);
    $("#legendcontainer").css("top", -11);
    $("#legendcontainer").css("transform", "scale(0.6)");
    $("#projection-resize").attr("onclick", "moveSide()");
}

function moveSide(){
    $("#projection-box").prependTo("#outer_center");
    changeSize(1100,1100);
    $("#resultbox").prependTo("#resultcontainer");
    $("#legendcontainer").css("left", 865);
    $("#legendcontainer").css("top", 20);
    $("#legendcontainer").css("transform", "scale(1)");
    $("#projection-resize").attr("onclick", "moveUp()");
}

function chooseSpecies(speciesname){
    
    $("#calculating").show();
    
    $("#speciesinfo").fadeOut(function() {
      $(this).text(speciesname.capitalize()).fadeIn();
    });
    
    $( "#center" ).fadeOut( "slow", function() {
        colorSets = [];
        pointGeo = new THREE.Geometry();
        startColor = ["#ffc700","#099DD7","#dd33dd","#248E84","#F2583F","#96503F"]
        colorCounter = 0
        
        colorNames = [];
        scatterPlot = new THREE.Object3D();

        document.getElementById("colorlist").innerHTML = "";
        document.getElementById('resultlist').innerHTML = "";

        renderer.setClearColor(new THREE.Color(0xffffff));
        document.getElementById("center").appendChild(renderer.domElement);

        camera = new THREE.PerspectiveCamera(45, w / h, 1, 10000);
        camera.position.z = 137;
        camera.position.x = -60;
        camera.position.y = 100;

        scene = new THREE.Scene();

        scatterPlot = new THREE.Object3D();
        scene.add(scatterPlot);

        scatterPlot.rotation.y = 0;

        function v(x, y, z) {
            return new THREE.Vector3(x, y, z);
        }

        dataset = [];
        sampleID = [];

        if(speciesname == "mouse"){
            file = "test1000_mouse.csv";
            activeSpecies = "mouse";
        }
        else{
            file = "test1000.csv";
            activeSpecies = "human";
        }

        d3.csv(file, function(d) {
            
            d.forEach(function (d,i) {
                dataset[i] = [ +d["x"], +d["y"], +d["z"], +d["samples"] ];
                sampleID[i] = parseInt(d["samples"]);
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
            
            var mat2 = new THREE.PointsMaterial({
                vertexColors: true,
                size: 1.5
            });
            
            var mats = [];

            var pointCount = dataset.length;
            
            
            for (var i = 0; i < pointCount; i ++) {
                var x = xScale(dataset[i][0]);
                var y = yScale(dataset[i][1]);
                var z = zScale(dataset[i][2]);

                pointGeo.vertices.push(new THREE.Vector3(x, y, z));
                
                if(dataset[i][3] != 0){
                    pointGeo.colors.push(new THREE.Color(0,0,0));
                    
                    var mat3 = new THREE.PointsMaterial({
                        vertexColors: true,
                        size: 1,
                    });
                    mats.push(mat3);
                }
                else{
                    pointGeo.colors.push(new THREE.Color(1,0,0));
                    var mat3 = new THREE.PointsMaterial({
                        vertexColors: true,
                        size: 1.5
                    });
                    mats.push(mat3);
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
            };
            cent.onmouseup = function() {
                down = false;
            };
            cent.onmousemove = function(ev) {
                if (down) {
                    var dx = ev.clientX - sx;
                    var dy = ev.clientY - sy;
                    scatterPlot.rotation.y += dx * 0.01;
                    camera.position.y += dy;
                    sx += dx;
                    sy += dy;
                }
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
        });
        $("#center").fadeIn("fast", null);
        $("#calculating").hide();
    });
}

String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

function add_search_main(text){
    
    var st = document.createElement('div');
    //st.style.float = "left";
    st.style.fontSize = 24;
    st.style.width = 300;
    st.style.backgroundColor = "green";
    st.innerHTML = text;
    
    var st2 = document.createElement('div');
    //st2.style.float = "right";
    st2.style.fontSize = 20;
    st2.id = text+"-samplecounts";
    st2.style.width = 300;
    st2.style.height = 50;

    var brt = document.createElement('div');
    brt.style.width = 320;
    brt.style.height = 130;
    brt.style.float = "right";
    
    var st3 = document.createElement('div');
    st3.style.float = "right";
    st3.style.fontSize = 20;
    st3.id = text+"-seriescounts";
    st3.style.width = 300;
    st3.style.height = 50;
    
    var co = document.createElement('div');
    co.className = "codedisplay";
    
    var cotitle = document.createElement('p');
    cotitle.innerHTML = "Download script";
    //co.appendChild(cotitle);
    
    var pp = document.createElement('pre');
    pp.id = text+"-codeSnippet";
    pp.style.padding = 20;
    pp.className = "prettyprint";
    co.appendChild(pp);
    
    var bu = document.createElement('button');
    bu.className = "btn clipboard";
    bu.innerHTML = "Copy";
    bu.id = text+"-copy";
    bu.name = "";
    //bu.onclick = copyToClipboard;

    var searchresult = document.createElement('div');
    searchresult.id = text+"-result";
    searchresult.width = 980;
    brt.appendChild(st2);
    searchresult.appendChild(bu);
    brt.appendChild(st3);
    searchresult.appendChild(st);
    searchresult.appendChild(brt);
    searchresult.appendChild(co);
    
    document.getElementById("resultlist").appendChild(searchresult);
    
    //makeWordCloud(searchresult.id);
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

function search_samples(form){

    $("#calculating").show();
    text = document.getElementById('term').value;
    var index = colorNames.indexOf(text.replace(/ /g, ""));
    if(index == -1){
        get_series(text);
        searchSamples(text);
        add_search(text);
    }
}

function search_similar_samples(form){
    $("#calculating").show();
    text = document.getElementById('signame').value;
    var index = colorNames.indexOf(text.replace(/ /g, ""));
    if(index == -1){
        search_similar(text);
        add_search(text);
    }
}

function addLegend(){
    
    legend = document.getElementById("legend");
    var csets =document.getElementsByTagName("input");

    while (legend.hasChildNodes()) {
        legend.removeChild(legend.lastChild);
    }

    for (var j = 0; j < colorNames.length; j++){
        legend.appendChild(getLabel($("#"+colorNames[j]).spectrum("get").toHexString(), colorNames[j]));
        legend.appendChild(document.createElement('br'));
    }
}

function searchSamples(searchterm){
    add_search_main(searchterm);
    
    $.getJSON("getSampleMatch.php?search="+searchterm+"&species="+activeSpecies, function(data){
        var samples = data;
        colorSets[samples[0]] = samples[1];
        changeColor(samples[1], pointGeo, $("#"+searchterm.replace(/ /g, "")).spectrum("get").toHexString());
        console.log(data);
        var samp = samples[1].map(function (i){
            return 'GSM' + i;
        })
        
        getCode("buildExpressionMatrix.r", samp, searchterm);

        document.getElementById(searchterm+"-samplecounts").innerHTML = "Samples: "+samples[1].length;
        $("#calculating").hide();
    });
}


function getCode(file, samples, searchterm){
    $.ajax({
        url : file,
        dataType: "text",
        success : function (data) {
            var allText = data;
            
            var joincounter = 1;
            var newString = "\"";
            for(var i=0; i < Math.min(1000,samples.length); i++){
                
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
            
            //var sampleString = "\""+samples.join("\",\"")+"\"";
            allText = allText.replace("insert_samples", newString);
            allText = allText.replace("searchterm", searchterm);
            allText = allText.replace(/selected_species/g, activeSpecies);
            $("#"+searchterm+"-codeSnippet").html(allText);
            $("#"+searchterm+"-copy").attr("data-clipboard-text", allText);

            prettyPrint();
        }
    });
}

function copyToClipboard() {
    try{
        window.clipboardData.setData('text', copyStrings[this.id] );
        alert("Succes!");
    }catch(e){
        alert("Your browser doesn't support copying towards the clipboard. Copy the text manually.")
    }
}

function add_search(text){

    var sampleset = document.createElement('li');
    sampleset.id = "cset";
    sampleset.style.backgroundColor = "white";
    sampleset.style.width = 280;
    sampleset.style.height = 40;
    sampleset.style.padding = 5;
    sampleset.style.borderBottom = "1px solid #lightgrey"
    sampleset.style.color = "black";
    
    var colo = document.createElement('div');
    colo.style.float="left";

    colo.innerHTML = "<input type='text' id='"+text.replace(/ /g, "")+"' />";
    colorNames.push(text.replace(/ /g, "")); 
    
    var text2 = document.createElement('div');
    text2.innerHTML = text;
    text2.style.float = "left";
    text2.style.onselectstart="return false";
    text2.style.padding = 5;
    
    sampleset.appendChild(colo);
    sampleset.appendChild(text2);
    
    var img = document.createElement('img');
    img.src = 'images/remove.png';
    img.style.float = "right";
    img.className = "img1";
    img.style.cursor = "pointer";
    
    img.addEventListener('click', function (e) {
        var elem = e.target.parentNode;
        elem.parentNode.removeChild(elem);
        $("#"+colorNames[i]+"").remove();

        var index = colorNames.indexOf(elem.getElementsByTagName("input")[0].getAttribute("id"));
        changeColor(colorSets[colorNames[index]], pointGeo, "#000000");

        colorNames.splice(index, 1);
        colorSets.splice(index, 1);
        
        for(var i=0;i<colorNames.length; i++){
            changeColor(colorSets[colorNames[i]], pointGeo, $("#"+colorNames[i]).spectrum("get").toHexString());
        }
        
        addLegend();
    });
    
    sampleset.appendChild(img);
    
    document.getElementById("colorlist").appendChild(sampleset);
    
    $("#"+text.replace(/ /g, "")).on("change", function() {
        changeColor(colorSets[text.replace(/ /g, "")], pointGeo, $("#"+text.replace(/ /g, "")).spectrum("get").toHexString());
        document.getElementById(text.replace(/ /g, "")+"-result").style.borderLeft = "20px solid"+$("#"+text.replace(/ /g, "")).spectrum("get").toHexString();
        addLegend();
    }); 
    
    $("#"+text.replace(/ /g, "")).spectrum({
        color: startColor[colorCounter % startColor.length]
    });
    colorCounter = colorCounter + 1;
}

function search_similar(text){
    var jsonData = {}
    var arrayGenes = $('#upGenes').val().split('\n');
    jsonData["type"] = "geneset";
    jsonData["species"] = activeSpecies;
    jsonData["signatureName"] = text;
    jsonData["genes"] = arrayGenes;
    
    $.ajax({
        type: "POST",
        url: "http://amp.pharm.mssm.edu/custom/rooky",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        jsonData,
        success: function(json) {
            var samples = data;
            colorSets[samples[0]] = samples[1];
            changeColor(samples[1], pointGeo, $("#"+searchterm.replace(/ /g, "")).spectrum("get").toHexString());
            $("#calculating").hide();
        },
        error: function (xhr, textStatus, errorThrown) {
            $("#error").html(xhr.responseText);
        }
    });
}

function get_series(searchterm){
    $.getJSON("getSeriesMatch.php?search="+searchterm+"&species="+activeSpecies, function(data){
        var series = data;
        document.getElementById(searchterm+"-seriescounts").innerHTML = "Series: "+series[1].length;
    });
}





