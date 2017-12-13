library(Rook)


app <- function(env) {

  req <- Request$new(env)
  res <- Response$new()

  input = fromJSON(rawToChar(env[['rook.input']]$postBody))

  upgenes <- input$upgenes
  downgenes <- input$downgenes
  direction <- input$direction
  species <- input$species
  
  gene <- input$gene
  
  print("function call")
  print(nchar(gene))
  
  if(!is.null(gene)){
    print("gene correlation")
    ll = list()
    
    gg = gene %in% rownames(cc)
    
    if(gg){
      ll[[1]] = rownames(cc)
      ll[[2]] = cc[gene,]
      
    } else if(gene %in% rownames(human_correlation)){
      ll[[1]] = rownames(human_correlation)
      ll[[2]] = human_correlation[gene,]
    } else{
      ll[[1]] = ""
    }
    
    body <- toJSON(ll)
    ret <- paste0(body, "\n");
    
    res$header("Content-type", "application/json")
    res$write(ret)

    res$finish()
  }
  else{
    
    if(species == "human"){
      genes = humangenes
      diffdown = humandiff[[2]]
      diffup = humandiff[[1]]
    }
    else if(species == "mouse"){
      genes = mousegenes
      diffdown = mousediff[[2]]
      diffup = mousediff[[1]]
    }

    upinter = intersect(genes, upgenes)
    downinter = intersect(genes, downgenes)
    
    ild = rep(0, length(diffdown))
    ilu = rep(0, length(diffdown))

    for(i in 1:length(diffdown)){
        ild[i] = length(intersect(diffdown[[i]], downgenes))
        ilu[i] = length(intersect(diffup[[i]], upgenes))
    }
    
    susu = scale(scale(ild)+scale(ilu))
    ww = which(susu > 4)
    
    samples = names(diffup)[ww]
    
    ll = list()
    
    ll[[1]] = input$signatureName
    names(ll)[1] = "name"
    
    ll[[2]] = as.numeric(gsub("GSM","",samples))
    names(ll)[2] = "samples"
    
    body <- toJSON(ll)
    ret <- paste0(body, "\n");
    
    res$header("Content-type", "application/json")
    res$write(ret);

    res$finish()
  }
}


