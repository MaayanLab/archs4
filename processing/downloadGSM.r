setwd("~/OneDrive/archs4final")

library("rhdf5")
library("Rtsne")
library("preprocessCore")
library("RMySQL")
library("tcltk")
library("GEOquery")

all_cons <- dbListConnections(MySQL())
for(con in all_cons){
    dbDisconnect(con)
}

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')


file = paste0("mouse_matrix.h5")

res = h5ls(file)
nsamples = as.numeric(res[4,5])
ngenes = as.numeric(res[5,5])

samples = h5read(file, "meta/Sample_geo_accession")
genes = h5read(file, "meta/genes")

H5close()


gsm_meta = list()

for(sa in samples){
    tt = tryCatch({
        geo = getGEO(sa, destdir="~/OneDrive/archs4final/geo")
        gsm_meta[[length(gsm_meta)+1]] = Meta(geo)
        names(gsm_meta)[length(gsm_meta)] = sa
    }, warning = function(w) {
    }, error = function(e) {
    }, finally = {})
}

save(gsm_meta, file="mouse_gsm_meta.rda")



file = paste0("human_matrix.h5")

res = h5ls(file)
nsamples = as.numeric(res[4,5])
ngenes = as.numeric(res[5,5])

samples = h5read(file, "meta/Sample_geo_accession")
genes = h5read(file, "meta/genes")

H5close()



gsm_meta = list()

for(sa in samples){
    tt = tryCatch({
        geo = getGEO(sa, destdir="~/OneDrive/archs4final/geo")
        gsm_meta[[length(gsm_meta)+1]] = Meta(geo)
        names(gsm_meta)[length(gsm_meta)] = sa
    }, warning = function(w) {
    }, error = function(e) {
    }, finally = {})
}

save(gsm_meta, file="human_gsm_meta.rda")











