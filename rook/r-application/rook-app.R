library(Rook)


app <- function(env) {

  req <- Request$new(env)
  res <- Response$new()

  input = fromJSON(rawToChar(env[['rook.input']]$postBody))

  print(input)

  gene <- input$gene
  
  print("function call")

  if(!is.null(gene)){
    print("gene correlation")
    ll = list()
    ll[[1]] = "OK"
    names(ll) = "Gene mode"
    body <- toJSON(ll)
    ret <- paste0(body, "\n");
    
    res$header("Content-type", "application/json")
    res$write(ret)

    res$finish()
  }
  else{
    print("signature similarity")
    upgenes <- input$upgenes
    downgenes <- input$downgenes
    direction <- input$direction
    species <- input$species
    
    if(species == "human"){
      zexp = hzexp
    }
    else if(species == "mouse"){
      zexp = mzexp
    }

    upinter = intersect(rownames(zexp), upgenes)
    downinter = intersect(rownames(zexp), downgenes)
    
    ll = list()
    
    if(length(c(upinter,downinter) > 1)){
      print("find similar sample")
      upscale = colSums(zexp[upinter,])
      downscale = colSums(zexp[downinter,])
      
      if(direction == "similar"){
          total = scale(upscale - downscale)
      }
      else{
          total = scale(downscale - upscale)
      }

      oo = which(total > 3)
      
      ll[[1]] = input$signatureName
      names(ll)[1] = "name"
      
      ll[[2]] = as.numeric(gsub("GSM","",colnames(zexp)[oo]))
      names(ll)[2] = "samples"
    }
    else{

      ll[[1]] = input$signatureName
      names(ll)[1] = "name"
      
      ll[[2]] = ""
      names(ll)[2] = "samples"
    }
    
    body <- toJSON(ll)
    ret <- paste0(body, "\n");
    
    res$header("Content-type", "application/json")
    res$write(ret);

    res$finish()
  }
}



