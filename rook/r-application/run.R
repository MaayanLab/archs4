library(Rook)
library(jsonlite)

.Call(tools:::startHTTPD, "0.0.0.0", 8080)


# Check if gene expression file was already downloaded, if not in current directory download file form repository
if(!file.exists("compressed_human_expression_1000.rda")){
    print("Downloading compressed gene expression matrix.")
    url = "https://s3.amazonaws.com/mssm-seq-matrix/compressed_human_expression_1000.rda"
    download.file(url, "compressed_human_expression_1000.rda", quiet = FALSE)
}
if(!file.exists("compressed_mouse_expression_1000.rda")){
    print("Downloading compressed gene expression matrix.")
    url = "https://s3.amazonaws.com/mssm-seq-matrix/compressed_mouse_expression_1000.rda"
    download.file(url, "compressed_mouse_expression_1000.rda", quiet = FALSE)
}
if(!file.exists("mouse_correlation.rda")){
    print("Downloading compressed gene expression matrix.")
    url = "https://s3.amazonaws.com/mssm-seq-matrix/mouse_correlation.rda"
    download.file(url, "mouse_correlation.rda", quiet = FALSE)
}
if(!file.exists("human_sub_correlation.rda")){
    print("Downloading compressed gene expression matrix.")
    url = "https://s3.amazonaws.com/mssm-seq-matrix/human_sub_correlation.rda"
    download.file(url, "human_sub_correlation.rda", quiet = FALSE)
}

res = load("compressed_human_expression_1000.rda")
print("human ready")

res = load("compressed_mouse_expression_1000.rda")
print("mouse ready")

res = load("mouse_correlation.rda")
res = load("human_sub_correlation.rda")
print("correlation ready")

print("Preloaded and ready, server booted and configured.")


s <- Rhttpd$new()
s$add(RhttpdApp$new(name="rooky", app="johnsonlindenstrauss.R"))

while (TRUE) {
  Sys.sleep(.Machine$integer.max)
}