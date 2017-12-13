# set the folder of this script as working directory.

library(Rook)
library(Matrix)
library(jsonlite)
library(proxy)
library(reshape)

# config for cpcd-gse70138-v1.0
config <- list(binaryMat="data/sparseMat_both_significant_and_insignificant_CD.rds",
               cdMat="data/mat_both_significant_and_insignificant_CD.rds",
               port=23243)
# # config for cpcd-v1.0
# config <- list(binaryMat="data/sparseMat_cpcd.rds",
#                cdMat="data/mat_cpcd_CD.rds",
#                port=23235)

# load variables --------------------------------------------------------
#used by both
#uniqGenes = fromJSON("genes.json")
#used by geneSet
#sparseMat <- readRDS(config$binaryMat)
#upDnGenesRef <- c(uniqGenes,uniqGenes)
#used by both
#sig_ids <- colnames(sparseMat)

#used by CD
#fullMat <- as.matrix(readRDS(config$cdMat))
#fullMat2 <- fullMat^2

# load functions ---------------------------------------------------------
#source('topGeneSetCombinations.R')
#source('topGeneSetMatch2.R')
#source('topCombinations.R')
#source('topCDMatch.R')
#source('topCDrank.R')


# benchmark --------------------------------------------------------------
#benchmarkInput <- fromJSON('./test/ebovs.json')

#use revoR halves the time
#res <- sapply(1:10,function(i){
#  ptm <- proc.time()
#  topCDMatch(benchmarkInput$ebov30min,'reverse',TRUE)
#  (proc.time()-ptm)[3]
#})

#print('expected time for computing CD result in seconds is:')
#print(median(res))


# server start ------------------------------------------------------------

s <- Rhttpd$new()
# run on R base to listen on the designated port number
s$start(listen='127.0.0.1',port=config$port)


my.app <- function(env){

  req <- Request$new(env)
  res <- Response$new()
  res$header("Access-Control-Allow-Origin","*")
  res$header("Access-Control-Allow-Methods","GET,PUT,POST,DELETE")


  if ("x" %in% names(req$GET())) {
    x <- as.numeric(req$GET()[["x"]])
    res <- Response$new()
    res$header("Content-type", "text-html")
    res$write(paste0("<p>If we double x, we get ", x*2, "!</p>"))
    res$finish()
  }
  else{
    res <- Response$new()
    res$header("Content-type", "text-html")
    res$write(paste0("<p>Try with x</p>"))
    res$finish()
  }


  # req$POST() is too slow to parse form encoded string
  # unpackage json data from form encoded string "json={...}". Skip 5 characters "json="
#  input <- fromJSON(substring(rawToChar(env[['rook.input']]$postBody),6))
#  combination <- input$combination
#  method <- input$method
#  if(method=='geneSet'){
#    upGenes <- input$upGenes
#    dnGenes <- input$dnGenes

#    print('geneSet')
#    ptm <- proc.time()
#    res$write(topGeneSetMatch(upGenes,dnGenes,combination))
#    print(proc.time()-ptm)
#  }else if(method=="CD"){
#    print('CD')
#    inputData <- input$input
    #   mimic == aggravate, reverse
#    direction <- input$direction

#    ptm <- proc.time()
#    res$write(topCDMatch(inputData,direction,combination))
#    print(proc.time()-ptm)

#  }
#   else if(method=="multi"){
#     print('Multi')
#     input <- fromJSON(req$POST()$input)
#     saveRDS(input,'input.rds')
#     res$write(getRP(input))
#     }else{
#     toJSON(list(err="method must be either geneSet or CD."))
#   }

#  res$finish()
}

s$add(app=my.app, name='Sigine')
## Open a browser window and display the web app
# s$browse('Sigine')

#print(getToHost("127.0.0.1","/custom"),port=23236)

while(TRUE) Sys.sleep(.Machine$integer.max)

