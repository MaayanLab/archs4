library("Rook")

s <- Rhttpd$new()
s$start(listen='127.0.0.1')

my.app <- function(env){
    ## Start with a table and allow the user to upload a CSV-file
    req <- Request$new(env)
    res <- Response$new()


    ## Add functionality to upload CSV-file
    if (!is.null(req$POST())) {
        ## Read data from uploaded CSV-file
        #data <- req$POST()[["data"]]
        #data <- read.csv(data$tempfile)
    }
    
  
    ## Write the HTML output and
    ## make use of the googleVis HTML output.
    ## See vignette('googleVis') for more details
    res$write("hello world")
    res$finish()
}

s$add(app=my.app, name='test')
## Open a browser window and display the web app
s$browse('test')



















