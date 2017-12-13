library("rhdf5")
library("Rtsne")
library("preprocessCore")
setwd("~/OneDrive/archs4final")
library("RMySQL")
library("tcltk")
library("GEOquery")

all_cons <- dbListConnections(MySQL())
for(con in all_cons){
    dbDisconnect(con)
}

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')

author = "Alexander Lachmann"
lab = "Ma'ayan Lab - Icahn School of Medicine at Mount Sinai"
contact = "alexander.lachmann@mssm.edu"


version = "1.0"


for(organism in c("human","mouse")){

    destination_file = paste0(organism, "_matrix.h5")

    # Check if gene expression file was already downloaded, if not in current directory download file form repository
    if(!file.exists(destination_file)){
        print("Downloading compressed gene expression matrix.")
        url = paste0("https://s3.amazonaws.com/mssm-seq-matrix/",organism,"_matrix.h5")
        download.file(url, destination_file, quiet = FALSE)
    } else{
        print("Local file already exists.")
    }

    file = destination_file

    res = h5ls(file)
    nsamples = as.numeric(res[4,5])
    ngenes = as.numeric(res[5,5])

    samples = h5read(file, "meta/samples")
    tissue = h5read(file, "meta/tissue")
    genes = h5read(file, "meta/genes")

    H5close()

    sql = paste0("SELECT attribute FROM gsm WHERE gsmid='",samples[1],"'")
    rs = dbSendQuery(mydb, sql)
    d1 = unlist(fetch(rs, n=-1))
    do = unique(unlist(d1))

    sql = paste0("SELECT gsm,sra,listid  FROM samplemapping")
    rs = dbSendQuery(mydb, sql)
    mapping = fetch(rs, n=-1)

    sql = paste0("SELECT *  FROM sequencing")
    rs = dbSendQuery(mydb, sql)
    m2 = fetch(rs, n=-1)

    sql = paste0("SELECT *  FROM runinfo")
    rs = dbSendQuery(mydb, sql)
    qual = fetch(rs, n=-1)

    mm = match(mapping[,3], qual[,1])

    cc = cbind(mapping, qual[mm,])
    cc[is.na(cc)] = 0

    mm = match(cc[,3], m2[,1])
    m3 = cbind(cc, m2[mm,])
    m3[,13] = gsub("organism:","",m3[,13])

    rs = dbSendQuery(mydb, "SELECT * FROM runinfo WHERE naligned > 100000")
    runi = fetch(rs, n=-1)

    ww = which(m3[,3] %in% runi[,1])
    m3 = m3[ww,]

    ww = which(m3[,13] == organism)
    m3 = m3[ww,]

    all_samples = unique(m3[,1])
    new_samples = setdiff(all_samples, samples)


    lll = list()
    counter = 0
    for(sa in new_samples){
        
        counter = counter + 1
        if(counter %% 1000 == 0){
            print(counter)
        }
        
        me = tryCatch({
            gsm <- getGEO(sa, destdir="geo")
            
            me = Meta(gsm)
            names(me) = paste0("Sample_", names(me))
            me[[length(me)+1]] = sa
            names(me)[length(me)] = "^SAMPLE"
            print("ok")
            me
        }, warning = function(w) {
        }, error = function(e) {
            rr = rep(NA, length(do))
            names(rr) = do
            rr[1] = sa
            print("error")
            print(e)
            rr = rr
        }, finally = {
        })
        
        me = me[do]
        names(me) = do
        lll[[length(lll)+1]] = unlist(lapply(me, paste, collapse = "\t"))
    }

    ll = do.call(cbind, lll)
    rownames(ll) = do

    if(organism == "mouse"){
        organismid = "10090"
    } else {
        organismid = "9606"
    }

    ww = which(ll[11,] == organismid)

    ll = ll[,ww]


    h5createFile(paste0(organism, "_hiseq_int",version,".h5"))
    h5createGroup(paste0(organism, "_hiseq_int",version,".h5"),"info")
    h5createGroup(paste0(organism, "_hiseq_int",version,".h5"),"data")
    h5createGroup(paste0(organism, "_hiseq_int",version,".h5"),"meta")
    H5close()

    h5createDataset(paste0(organism, "_hiseq_int",version,".h5"), "data/expression", c(length(genes), length(samples) + length(ww)), chunk=c(200,200), storage.mode = "integer")
    H5close()


    seg = 200

    pb = tkProgressBar(min = 0, max = length(samples), initial = 0, title="Write old samples", label="0")
    time = Sys.time()
    for(i in 1:ceiling(length(samples)/seg)){
        
        if(i %% 10 == 1){
            setTkProgressBar(pb, i*seg, label=paste0(round(i*seg/length(samples)*100, 0),"% done (", i*seg, "/",length(samples),")"))
        }
        wk = ((i-1)*seg+1):min(length(samples),i*seg)
        print(wk)
        expt = h5read(file, "data/expression", index=list(1:length(genes), wk))
        h5write(expt, paste0(organism, "_hiseq_int",version,".h5"),"data/expression", index=list(1:length(genes),wk))
    }
    Sys.time() - time

    close(pb)
    H5close()


    mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')
    gsms = new_samples[ww]

    pb = tkProgressBar(min = 0, max = length(gsms), initial = 0, title="Write new samples", label="0")

    texp = list()
    counter = 0
    for(gsm in gsms){
        if(counter%%10 == 0){
            print(counter)
        }
        counter = counter + 1
        rs = dbSendQuery(mydb, paste0("SELECT kallistoquant.geneid AS geneid, genemapping.genesymbol AS genesymbol, SUM(kallistoquant.value) AS value FROM kallistoquant INNER JOIN genemapping ON kallistoquant.geneid=genemapping.geneid WHERE listid IN (SELECT listid FROM samplemapping WHERE gsm='", gsm,"') GROUP BY geneid"))
        dd = fetch(rs, n=-1)
        dd[,3] = round(dd[,3])
        oo = order(dd[,2])
        dd = dd[oo,]
        
        texp[[length(texp)+1]] = dd[,3]
        
        if(length(texp) == 200){
            setTkProgressBar(pb, counter, label=paste0(round(counter/length(gsms)*100, 0),"% done (", counter, "/",length(gsms),")"))
            exp = do.call(cbind, texp)
            texp = list()
            wk = (length(samples)+counter - ncol(exp)+1):(length(samples)+counter)
            print(wk)
            h5write(exp, paste0(organism, "_hiseq_int",version,".h5"),"data/expression", index=list(1:length(genes),wk))
        }
    }
    close(pb)

    exp = do.call(cbind, texp)
    texp = list()
    wk = (length(samples)+counter - ncol(exp)+1):(length(samples)+counter)
    print(wk)
    h5write(exp, paste0(organism, "_hiseq_int",version,".h5"),"data/expression", index=list(1:length(genes),wk))
    H5close()


    for(di in do[-1]){
        rh = h5read(file, paste0("meta/",gsub("\\/","-",di)))
        rh = unlist(c(rh,unlist(ll[di,])))
        h5write(rh, paste0(organism, "_hiseq_int",version,".h5"),paste0("meta/",gsub("\\/","-",di)))
    }
    h5write(genes, paste0(organism, "_hiseq_int",version,".h5"),"meta/genes")

    h5write(paste0("","1.0"), paste0(organism, "_hiseq_int", version ,".h5"),"info/version")
    h5write(paste0("",Sys.Date()), paste0(organism, "_hiseq_int",version,".h5"),"info/creation-date")
    h5write(author, paste0(organism, "_hiseq_int",version,".h5"),"info/author")
    h5write(contact, paste0(organism, "_hiseq_int",version,".h5"),"info/contact")
    h5write(lab, paste0(organism, "_hiseq_int",version,".h5"),"info/lab")
    h5close()

    h5ls(paste0(organism, "_hiseq_int",version,".h5"))


    rh = h5read(paste0(organism, "_hiseq_int",version,".h5"), "meta/Sample_characteristics_ch1")
    file = paste0(organism, "_hiseq_int",version,".h5")

    res = h5ls(file)

    samples = h5read(file, "meta/Sample_geo_accession")
    nsamples = length(samples)
    genes = h5read(file, "meta/genes")
    ngenes = length(genes)
    H5close()

    sa = sample(1:nsamples, 2000)

    expt = h5read(file, "data/expression", index=list(1:ngenes, sa))


    H5close()
    expt = log2(expt+1)
    expt = normalize.quantiles(expt)
    rownames(expt) = genes
    colnames(expt) = samples[sa]

    ww = which(rowSums(expt) > 500)


    tt = Rtsne(expt[ww,], dims=3, perplexity=30, check_duplicates = F)
    geneplot = tt$Y

    mx = cbind(round(geneplot, digits=3), genes[ww])
    colnames(mx) = c("x","y","z","gene")
    write.table(mx, file=paste0("gene_",organism,"_tsne_30_v",version,".csv"), quote=F, row.names=F, sep=",")


    ga = sample(ww, 2000)
    expt = h5read(file, "data/expression", index=list(ga, 1:nsamples))
    expt = log2(expt+1)
    expt = normalize.quantiles(expt)
    rownames(expt) = genes[ga]
    colnames(expt) = samples

    tt = Rtsne(t(expt), dims=3, perplexity=50, check_duplicates = F)
    sampleplot = tt$Y

    mx = cbind(round(sampleplot, digits=3), gsub("GSM","",samples))
    colnames(mx) = c("x","y","z","samples")
    write.table(mx, file=paste0("sample_",organism,"_tsne_50_v",version,".csv"), quote=F, row.names=F, sep=",")

    
    mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')
    
    rs = dbSendQuery(mydb, "SELECT * FROM sample_meta")
    metasamples = fetch(rs, n=-1)

    samples = h5read(file, "meta/Sample_geo_accession")
    series = h5read(file, "meta/Sample_series_id")
    tissue = h5read(file, "meta/Sample_source_name_ch1")
    tissue = gsub("'","",tissue)
    
    speciesinfo = rep(organism, length(samples))
    
    tissueMod = gsub("_","",tissue)
    tissueMod = gsub("-","",tissueMod)
    tissueMod = gsub("'","",tissueMod)
    tissueMod = gsub("-","",tissueMod)
    tissueMod = gsub("/","",tissueMod)
    tissueMod = gsub(" ","",tissueMod)
    tissueMod = gsub("\\.","",tissueMod)
    tissueMod = toupper(tissueMod)

    smeta = do.call(cbind, list(series, samples, tissue, tissueMod, speciesinfo))
    ww = which(!(smeta[,2] %in% metasamples[,3]))
    
    submeta = smeta[ww,]
    
    scounter = 0
    gsebuffer = ""
    for(i in 1:nrow(submeta)){
        scounter = scounter+1
        gsebuffer = paste0(gsebuffer, "('", paste(submeta[i,1], submeta[i,2], submeta[i,3], submeta[i,4], submeta[i,5] ,sep="','"), "'),")
        if(scounter > 1000){
            print(i)
            sql = paste0("INSERT INTO sample_meta (gse, gsm, tissue, tissue_mod, species) VALUES ", gsebuffer)
            sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
            rs = dbSendQuery(mydb, sql)
            scounter = 0
            gsebuffer = ""
        }
    }
    sql = paste0("INSERT INTO sample_meta (gse, gsm, tissue, tissue_mod, species) VALUES ", gsebuffer)
    sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
    rs = dbSendQuery(mydb, sql)
    
}



res = load("human_expression_compressed.rda")

mg = h5read("mouse_matrix.h5", "meta/genes")
hg = h5read("human_matrix.h5", "meta/genes")

inter = intersect(mg, hg)
inter = intersect(inter, )



library("rhdf5")
library("Rtsne")
library("preprocessCore")
setwd("~/OneDrive/archs4final")
library("RMySQL")
library("tcltk")
library("GEOquery")

all_cons <- dbListConnections(MySQL())
for(con in all_cons){
    dbDisconnect(con)
}

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')

author = "Alexander Lachmann"
lab = "Ma'ayan Lab - Icahn School of Medicine at Mount Sinai"
contact = "alexander.lachmann@mssm.edu"


version = "1.0"


organism = "mouse"

destination_file = paste0(organism, "_matrix.h5")

# Check if gene expression file was already downloaded, if not in current directory download file form repository
if(!file.exists(destination_file)){
    print("Downloading compressed gene expression matrix.")
    url = paste0("https://s3.amazonaws.com/mssm-seq-matrix/",organism,"_matrix.h5")
    download.file(url, destination_file, quiet = FALSE)
} else{
    print("Local file already exists.")
}

file = destination_file

res = h5ls(file)
nsamples = as.numeric(res[4,5])
ngenes = as.numeric(res[5,5])

samples = h5read(file, "meta/Sample_geo_accession")
genes = h5read(file, "meta/genes")

H5close()


sql = paste0("SELECT attribute FROM gsm WHERE gsmid='",samples[1],"'")
rs = dbSendQuery(mydb, sql)
d1 = unlist(fetch(rs, n=-1))
do = unique(unlist(d1))

sql = paste0("SELECT gsm,sra,listid  FROM samplemapping")
rs = dbSendQuery(mydb, sql)
mapping = fetch(rs, n=-1)

sql = paste0("SELECT *  FROM sequencing")
rs = dbSendQuery(mydb, sql)
m2 = fetch(rs, n=-1)

sql = paste0("SELECT *  FROM runinfo")
rs = dbSendQuery(mydb, sql)
qual = fetch(rs, n=-1)

mm = match(mapping[,3], qual[,1])

cc = cbind(mapping, qual[mm,])
cc[is.na(cc)] = 0

mm = match(cc[,3], m2[,1])
m3 = cbind(cc, m2[mm,])
m3[,13] = gsub("organism:","",m3[,13])


ww = which(m3[,1] == samples[1])
lt = paste0("https://s3.amazonaws.com/mssm-seq-results/",m3[ww,8],"-",m3[ww,9],"_kallisto.tsv")
download.file(lt, "temp.tsv", quiet = FALSE)
tt = read.table("temp.tsv", sep="\t", skip=1)


h5createFile(paste0(organism, "_hiseq_eid_",version,".h5"))
h5createGroup(paste0(organism, "_hiseq_eid_",version,".h5"), "info")
h5createGroup(paste0(organism, "_hiseq_eid_",version,".h5"), "data")
h5createGroup(paste0(organism, "_hiseq_eid_",version,".h5"), "meta")
H5close()

h5createDataset(paste0(organism, "_hiseq_eid_",version,".h5"), "data/expression", c(nrow(tt), length(samples)), chunk=c(200,200), storage.mode = "integer")
H5close()

seg = 200

pb = tkProgressBar(min = 0, max = length(samples), initial = 0, title="Write transcript level", label="0")
time = Sys.time()

for(i in 1:ceiling(length(samples)/seg)){
    #for(i in 1:20){
    
    print(i)
    
    setTkProgressBar(pb, i*seg, label=paste0(round(i*seg*100/length(samples), 0),"% done (", i*seg, "/",length(samples),")"))
    
    wk = ((i-1)*seg+1):min(length(samples),i*seg)
    
    countList = list()
    for(sa in samples[wk]){
        
        ww = which(m3[,1] == sa)
        links = paste0("https://s3.amazonaws.com/mssm-seq-results/",m3[ww,8],"-",m3[ww,9],"_kallisto.tsv")
        counts = rep(0, nrow(tt))
        print(sa)
        for(l in links){
            tryCatch({
                download.file(l, "temp.tsv", quiet = FALSE)
                tt = read.table("temp.tsv", sep="\t", skip=1, stringsAsFactors=F)
                counts = counts+tt[,4]
            }, warning = function(w) {
            }, error = function(e) {
            }, finally = {})
        }
        countList[[length(countList)+1]] = round(counts)
    }
    
    exp = do.call(cbind, countList)
    h5write(exp, paste0(organism, "_hiseq_eid_",version,".h5"),"data/expression", index=list(1:nrow(exp),wk))
}
Sys.time() - time

close(pb)
H5close()

h5write(as.character(tt[,1]), paste0(organism, "_hiseq_eid_",version,".h5"),"meta/ensemblid")
h5write(tt[,2], paste0(organism, "_hiseq_eid_",version,".h5"),"meta/transcriptlength")
H5close()

h5write(paste0("","1.0"), paste0(organism, "_hiseq_eid_", version ,".h5"),"info/version")
h5write(paste0("",Sys.Date()), paste0(organism, "_hiseq_eid_",version,".h5"),"info/creation-date")
h5write(author, paste0(organism, "_hiseq_eid_",version,".h5"),"info/author")
h5write(contact, paste0(organism, "_hiseq_eid_",version,".h5"),"info/contact")
h5write(lab, paste0(organism, "_hiseq_eid_",version,".h5"),"info/lab")
H5close()


hh = h5ls(file)
dd = hh[10:44,]

for(i in 1:nrow(dd)){
    ll = h5read(file, paste0("meta/",dd[i,2]))
    print(length(ll))
    h5write(ll, paste0(organism, "_hiseq_eid_",version,".h5"), paste0("meta/",dd[i,2]))
}
H5close()














