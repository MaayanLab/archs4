// ENTER_POINT = '/CREEDS';
var exampleGenes = {
    upGenes: ['AARS','ACLY','KIAA0907','KDM5A','CDC25A','EGR1','GADD45B','RELB','TERF2IP','SMNDC1','TICAM1','NFKB2','RGS2','NCOA3','ICAM1','TEX10','CNOT4','ARID4B','CLPX','CHIC2','CXCL2','FBXO11','MTF2','CDK2','DNTTIP2','GADD45A','GOLT1B','POLR2K','NFKBIE','GABPB1','ECD','PHKG2','RAD9A','NET1','KIAA0753','EZH2','NRAS','ATP6V0B','CDK7','CCNH','SENP6','TIPARP','FOS','ARPP19','TFAP2A','KDM5B','NPC1','TP53BP2','NUSAP1'],
    dnGenes: ['ACTN1','ACTG1','SCCPDH','KIF20A','FZD7','USP22','PIP4K2B','CRYZ','GNB5','EIF4EBP1','PHGDH','RRAGA','SLC25A46','RPA1','HADH','DAG1','RPIA','P4HA2','MACF1','TMEM97','MPZL1','PSMG1','PLK1','SLC37A4','GLRX','CBR3','PRSS23','NUDCD3','CDC20','KIAA0528','NIPSNAP1','TRAM2','STUB1','DERA','MTHFD2','BLVRA','IARS2','LIPA','PGM1','CNDP2','BNIP3','CTSL1','CDC25B','HSPA8','EPRS','PAX8','SACM1L','HOXA5','TLE1','PYGL','TUBB6','LOXL1']
};


$(document).ready(function(){
    // autocomplete for searching signatures
    

    $.getJSON("search/getGenes.php", function(data){
        var genes = data;
        $("#genesymbolsearch").autoComplete({
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

    toggleSearch("meta");
    
    $("#sigNameBtn").click(function(){
        
        text = document.getElementById('sigNameInput').value;
        termid = hashCode(text);
        if(!(termid in colorSets[colorID])){
            $("#calculating").show();
            //get_series(text,termid);
            searchSamples(text, termid);
            //add_search(text, termid);
        }
        else{
            alert("search already exists");
        }
    });

    $("#mouseSelect").click(function(){
        chooseSpecies("mouse", activeMode);
    });

    $("#humanSelect").click(function(){
        chooseSpecies("human", activeMode);
    });
    
    $("#sampleSelect").click(function(){
        chooseSpecies(activeSpecies, "sample");
    });

    $("#geneSelect").click(function(){
        chooseSpecies(activeSpecies, "gene");
    });

    $(".exampleTerm").click(function(){
        var term = $(this).text();
        $("#sigNameInput").val(term);
    });

    $("#geneSearchEgBtn").click(function(){
        $('#upGenes').val(exampleGenes.upGenes.join('\n'));
        $('#dnGenes').val(exampleGenes.dnGenes.join('\n'));
    });

    // search sigs using up/dn genes
    $(".geneSearchBtn").click(function(e){
        signame = document.getElementById('sigName').value;
        termid = hashCode(text);
        if(!(termid in colorSets[colorID])){
            search_similar(signame, termid);
            add_search(signame, termid);
        }
        else{
            alert("search already exists");
        }
    })
});

function fillSignatureExample(){
    $("#sigName").val("Example");
    $('#upGenes').val(exampleGenes.upGenes.join('\n'));
    $('#dnGenes').val(exampleGenes.dnGenes.join('\n'));
    searchSignature("similar");
}

function fillExample(text){
    $("#sigNameInput").val(text);
    searchMeta();
}

function searchSignature(direction){
    
    searchterm = document.getElementById('sigName').value;
    termid = hashCode(searchterm+"_"+direction);

    if(!(termid in colorSets[colorID])){
        $("#calculating").show();
        search_similar(searchterm+"_"+direction, direction, termid);
    }
    else{
        alert("search already exists");
    }
}

function searchMeta(){
    //moveUp();
    text = document.getElementById('sigNameInput').value;
    termid = hashCode(text);
    
    if(!(termid in colorSets[colorID])){
        $("#calculating").show();
        //get_series(text, termid);
        searchSamples(text, termid);
        //add_search(text, termid);
    }
    else{
        alert("search already exists");
    }
}

function search_similar_samples(form){
    //moveUp();
    
    text = document.getElementById('signame').value;
    termid = hashCode(text);
    
    if(!(termid in colorSets[colorID])){
        $("#calculating").show();
        search_similar(text, termid);
        add_search(text, termid);
    }
    else{
        alert("search already exists");
    }
}

function submitSelectedBtn (){
        $("#calculating").show();
        $("#submitSelectedBtn").remove();
        var btn = $("<a>");
        btn.text("Visualize results as a clustergram");
        btn.addClass('btn btn-info');
        btn.attr('href', '#clustergram');
        btn.attr('id', 'submitSelectedBtn');
        btn.click(function(){ // click to post genes and uids to clustergram vis
            
        var upGenes = $('#upGenes').val().split('\n');
        var dnGenes = $('#dnGenes').val().split('\n');      
        var genes = upGenes.concat(dnGenes);

        postPayLoad = {"genes": genes, "ids": uids}; // global

    });

    $("#searchResults").append(btn);

}