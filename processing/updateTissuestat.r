
setwd("~/OneDrive/archs4final")
library("GEOquery")
library("RColorBrewer")
library("RCurl")
library("RMySQL")
library("rhdf5")

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')

platforms = c()

for(species in c("Mouse","Human")){
    sql = paste0("SELECT species, tissue FROM tissuestat WHERE species='",species,"'")
    rs = dbSendQuery(mydb, sql)
    dd = fetch(rs, n=-1)

    # Retrieve information from compressed data
    samples = h5read(paste0(tolower(species), "_hiseq_int1.0.h5"), "meta/Sample_geo_accession")
    series = h5read(paste0(tolower(species), "_hiseq_int1.0.h5"), "meta/Sample_series_id")
    tissue = h5read(paste0(tolower(species), "_hiseq_int1.0.h5"), "meta/Sample_source_name_ch1")
    plat = h5read(paste0(tolower(species), "_hiseq_int1.0.h5"), "meta/Sample_platform_id")
    platforms = c(platforms, plat)
    
    tissueMod = gsub("_","",tissue)
    tissueMod = gsub("-","",tissueMod)
    tissueMod = gsub("'","",tissueMod)
    tissueMod = gsub("-","",tissueMod)
    tissueMod = gsub("/","",tissueMod)
    tissueMod = gsub(" ","",tissueMod)
    tissueMod = gsub("\\.","",tissueMod)
    tissueMod = toupper(tissueMod)
    
    for(i in 1:nrow(dd)){
        gg = grep(gsub(" ", "", dd[i,2]), tissueMod)
        print(length(gg))
        sql = paste0("UPDATE tissuestat SET count = '",length(gg),"' WHERE species='",species,"' AND tissue = '",dd[i,2],"';")
        rs = dbSendQuery(mydb, sql)
    }
}

platforms = rev(sort(table(platforms)))

for(i in 1:4){
    sql = paste0("UPDATE platformstat SET count = '",platforms[i],"' WHERE platform='",names(platforms)[i],"';")
    rs = dbSendQuery(mydb, sql)
}

