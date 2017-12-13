setwd("~/OneDrive/findsamples")

library("RMySQL")
library("SRAdb")
library("digest")
library("GEOquery")
sqlfile <- getSRAdbFile()

all_cons <- dbListConnections(MySQL())
for(con in all_cons){
    dbDisconnect(con)
}

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')

#sql = paste0("SELECT DISTINCT(datalinks) FROM sequencing WHERE id IN (SELECT listid FROM runinfo WHERE naligned > 0)")
sql = paste0("SELECT DISTINCT id, datalinks FROM sequencing")
rs = dbSendQuery(mydb, sql)
mm = fetch(rs, n=-1)
mysamples = mm[,2]
sras = gsub(".sra","",sapply(strsplit(mysamples, "/"), tail, 1))
sraids = mm[,1]
names(sraids) = sras

sra_con <- dbConnect(SQLite(),"SRAmetadb.sqlite")
dbListTables(sra_con)
res <- dbGetQuery(sra_con, "SELECT study_alias, sample_alias, run_accession, updated_date, taxon_id FROM sra_ft WHERE library_strategy='RNA-Seq' AND instrument_model IN ('Illumina HiSeq 2000', 'Illumina HiSeq 2500') AND taxon_id IN (9606,10090)")


sql = paste0("SELECT DISTINCT(sra) FROM samplemapping")
rs = dbSendQuery(mydb, sql)
sra_already = unlist(fetch(rs, n=-1))

sql = paste0("SELECT MAX(listid) FROM samplemapping")
rs = dbSendQuery(mydb, sql)
maxlistid = unlist(fetch(rs, n=-1))

#-------------------------

ww = grep("SRR",res[,3])
clean = res[ww,]

ww = grep("GSM",clean[,2])
clean = clean[ww,]

ww = grep("GSE",clean[,1])
clean = clean[ww,]

still = which(!(clean[,3] %in% sra_already))
missing = clean[still,]

sqlbuffer = ""
counter = 0
listidcounter = maxlistid
for(i in 1:nrow(missing)){
    listidcounter = listidcounter+1
    counter = counter + 1
    sqlbuffer = paste0(sqlbuffer, sprintf("('%s', '%s', '%s', %s),", missing[i,1], missing[i,2], missing[i,3], listidcounter))

    if(counter > 1000){
        sql = paste0("INSERT INTO samplemapping (gse, gsm, sra, listid) VALUES ", sqlbuffer)
        sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
        rs = dbSendQuery(mydb, sql)
        sqlbuffer = ""
        counter = 0
    }
}
sql = paste0("INSERT INTO samplemapping (gse, gsm, sra, listid) VALUES ", sqlbuffer)
sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
rs = dbSendQuery(mydb, sql)

# -------------------------------

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')


sql = paste0("SELECT sra, listid FROM samplemapping")
rs = dbSendQuery(mydb, sql)
new_mapping = fetch(rs, n=-1)

mm = match(missing[,3], new_mapping[,1])
ct = cbind(missing, new_mapping[mm,])

ww = which(!(ct[,3] %in% sras))
new_sras = ct[ww,]

sqlbuffer = ""
counter = 0


for(i in 1:nrow(new_sras)){
    
    counter = counter+1
    sid = new_sras[i,7]
    uid = digest(new_sras[i,3])
    datalink = paste0("ftp://ftp-trace.ncbi.nih.gov/sra/sra-instant/reads/ByRun/sra/SRR/",substr(new_sras[i,3], 1,6),"/",new_sras[i,3],"/",new_sras[i,3],".sra")
    parameters = "organism:human"
    status = "waiting"
    if(new_sras[i,5] == "10090"){
        parameters = "organism:mouse"
    }
    sqlbuffer = paste0(sqlbuffer, sprintf("('%s','%s', '%s', '%s', '%s', '%s'),", sid, uid, "seq-results", datalink, parameters, status))

    if(counter > 100){
        sql = paste0("INSERT INTO sequencing (id, uid, resultbucket, datalinks, parameters, status) VALUES ", sqlbuffer)
        sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
        rs = dbSendQuery(mydb, sql)
        sqlbuffer = ""
        counter = 0
    }
}
sql = paste0("INSERT INTO sequencing (id, uid, resultbucket, datalinks, parameters, status) VALUES ", sqlbuffer)
sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
rs = dbSendQuery(mydb, sql)



