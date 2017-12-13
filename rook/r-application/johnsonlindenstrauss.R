library("Rook")


app <- function(env) {

  req <- Request$new(env)
  res <- Response$new()

  input = fromJSON(rawToChar(env[['rook.input']]$postBody))

  upgenes <- input$upgenes
  downgenes <- input$downgenes
  direction <- input$direction
  species <- input$species
  
  gene <- input$gene

  print(gene)
  
  if(!is.null(gene)){
    print("gene correlation")
    ll = list()
    
    if(gene %in% rownames(cc)){
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
      genes = colnames(human_transform)
      expression = jl_human_expression
      transform = human_transform
    }
    else if(species == "mouse"){
      genes = colnames(mouse_transform)
      expression = jl_mouse_expression
      transform = mouse_transform
    }
    
    vec = genes %in% upgenes
    vec[genes %in% downgenes] = -1
    names(vec) = genes
    jl_vec = transform %*% vec
    
    similarity = scale(c(cor(jl_vec, expression)))
    names(similarity) = colnames(expression)
    ww = which(similarity > 2.5)
    
    samples = names(similarity)[ww]
    
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



