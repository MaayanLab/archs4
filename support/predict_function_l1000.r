setwd("~/OneDrive/archs4final")

library("RMySQL")
library("SRAdb")
library("digest")
library("GEOquery")
library("RColorBrewer")
library("plyr")
library("preprocessCore")
library("polyclip")
library("caTools")
library("rhdf5")

cols <- brewer.pal(8,"Set1")

range01 <- function(x){(x-min(x))/(max(x)-min(x))}

getROC <- function(expt, gmtf, polish=F, folder="auc", suffix="", doplot=F, writesql=F, correlation=F, removeDiag=T){
    
    dir.create(file.path(folder), showWarnings = FALSE)
    gmt = readLines(gmtf)
    genes = colnames(expt)
    go = list()
    for(ll in gmt){
        sp = unlist(strsplit(ll, "\t"))
        gene = sp[1]
        t = c()
        for(i in 3:length(sp)){
            sp1 = unlist(strsplit( sp[i], ","))
            t = c(t, sp1[1])
        }
        ig = intersect(genes, t)
        if(length(ig) > 1){
            go[[length(go)+1]] = intersect(genes, t)
            names(go)[length(go)] = gsub("_$", "", gene)
        }
    }
    
    geneo = list()
    for(tt in names(go)){
        for(g in go[[tt]]){
            if(is.null(geneo[[g]])){
                geneo[[g]] = c(tt)
            }
            else{
                geneo[[g]] = c(geneo[[g]],tt)
            }
        }
    }
    
    if(correlation){
        correlation_matrix = expt
    }
    else{
        print("building correlation matrix")
        correlation_matrix = cor(t(expt), t(expt))
    }
    
    if(polish){
        print("apply median polish")
        correlation_matrix = medpolish(correlation_matrix)$residuals
    }
    
    if(removeDiag){
        diag(correlation_matrix) = NA
    }
    
    mean_matrix = matrix(0, nrow(correlation_matrix), length(go))
    rownames(mean_matrix) = rownames(correlation_matrix)
    colnames(mean_matrix) = names(go)
    
    for(p in 1:length(go)){
        mean_matrix[, p] = rowMeans(correlation_matrix[,go[[p]]], na.rm=T)
    }
    
    gl = c(unlist(lapply(geneo, length)))
    wgl = which(gl >= 5)
    fgeneo = geneo[wgl]
    
    print("calculate AUC statistics")
    aucs = c()
    for(p in names(fgeneo)){
        
        cu = rep(0, ncol(mean_matrix))
        names(cu) = names(rev(sort(mean_matrix[p,])))
        setinter = intersect(fgeneo[[p]], names(cu))
        cu[setinter] = 1
        cs = cumsum(cu)
        
        x = 1:length(cs)
        y = cs
        auc = trapz(range01(x),range01(y))
        
        if(doplot){
            png(paste0(folder,"/roc_",suffix,"_",p,".png"), 600,400)
            plot(range01(1:length(cs)), range01(cs), type="l", xlim=c(0,1), ylim=c(0,1), lwd=3, xlab="1 − Specificity", ylab="Sensitivity", main="", cex.lab=1.4, cex.axis=1.4)
            mtext(side=3,line=1, p, cex=1.4)
            abline(0,1, col="red", lty=2, lwd=2)
            legend("bottomright", legend=c(paste("AUC =", format(sum(cs/max(cs))/length(cs), digits=3))), bty = "n", cex=1.6)
            dev.off()
        }
        aucs = c(aucs, auc)
    }
    names(aucs) = names(fgeneo)
    
    if(writesql){
        mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.rds.amazonaws.com')
    
        bufferstep = 0
        buffer = ""
        for(i in 1:nrow(mean_matrix)){
            bufferstep = bufferstep+1
            igene = rownames(mean_matrix)[i]
            su = scale(mean_matrix[i,])
            
            names(su) = colnames(mean_matrix)
            names(su) = gsub("'", "", names(su))
            su = rev(sort(su))[1:100]
            ww = as.numeric(names(su) %in% geneo[[rownames(mean_matrix)[i]]])
            
            for(j in 1:100){
                buffer = paste0(buffer, paste0("('",igene,"','",suffix,"','",names(su)[j],"','",ww[j],"','",su[j],"'),"))
            }
            
            if(bufferstep > 20){
                sql = paste0("INSERT INTO functional_prediction (gene, listtype, geneset, termmatch, zscore) VALUES ", buffer)
                sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
                rs = dbSendQuery(mydb, sql)
                bufferstep = 0
                buffer = ""
            }
        }
        sql = paste0("INSERT INTO functional_prediction (gene, listtype, geneset, termmatch, zscore) VALUES ", buffer)
        sql = paste0(substr(sql, 1, nchar(sql)-1), ";")
        rs = dbSendQuery(mydb, sql)
    }
    return(aucs)
}


res = load("../dtoxs/data/l1000/sl1000.rda")
res = load("../dtoxs/data/l1000/gan_l1000.rda")
res = load("human_archs4.rda")
res = load("../dtoxs/l1000original.rda")

inter = intersect(rownames(ganexp), rownames(sl1000))
inter = intersect(inter, rownames(archs4))
inter = intersect(inter, rownames(lexp))

nsl1000 = normalize.quantiles(sl1000[inter,])
ngan_l1000 = normalize.quantiles(ganexp[inter,])
narchs4 = normalize.quantiles(archs4[inter,])
nlexp = normalize.quantiles(lexp[inter,])

rownames(nsl1000) = inter
rownames(ngan_l1000) = inter
rownames(narchs4) = inter
rownames(nlexp) = inter

res = load("human_correlation.rda")
correlation_matrix = cc
diag(correlation_matrix) = NA

mydb = dbConnect(MySQL(), user='xxx', password='yyy', dbname='alignment_pipeline', host='zzz.amazonaws.com')
sql = "SELECT DISTINCT(gene) FROM functional_prediction;"
rs = dbSendQuery(mydb, sql)
sqlgenes = fetch(rs, n=-1)

ww = which(!(rownames(correlation_matrix) %in% unique(sqlgenes[,1])))
correlation_matrix = correlation_matrix[ww,]

wkk = which((rownames(correlation_matrix) %in% unique(sqlgenes[,1])))
rownames(correlation_matrix)[wkk][1]



gmtfs = c("gmt/GO_Biological_Process_2015.txt", "gmt/ChEA_2016.txt", "gmt/KEGG_2016.txt", "gmt/Human_Phenotype_Ontology.txt" ,"gmt/KEA_2015.txt", "gmt/MGI_Mammalian_Phenotype_Level_4.txt")
suffixes = c("go_bio", "chea", "kegg", "humph", "kea", "mgi")


ccl1000 = cor(t(nsl1000))
ccgan = cor(t(ngan_l1000))
ccarchs4 = cor(t(narchs4))


cors = list(ccl1000, ccgan, ccarchs4)
roro = list()

for(correlation_matrix in cors){
    diag(correlation_matrix) = NA
    rocs = list()
    for(g in 1:length(gmtfs)){
        gmt = gmtfs[g]
        suffix = suffixes[g]
        print(gmt)
        roc = getROC(correlation_matrix, gmt, polish=F, folder="auc", suffix, doplot=F, writesql=F, correlation=T, removeDiag=F)
        rocs[[length(rocs)+1]] = roc
    }
    names(rocs) = suffixes
    roro[[length(roro)+1]] = rocs
}
save(roro, file="auc_prediction_human.rda")




cors = list(nlexp, ngan_l1000, narchs4)
roro = list()

for(cdd in cors){
    correlation_matrix = cor(t(cdd))
    diag(correlation_matrix) = NA
    rocs = list()
    for(g in 1:length(gmtfs)){
        gmt = gmtfs[g]
        suffix = suffixes[g]
        print(gmt)
        roc = getROC(correlation_matrix, gmt, polish=F, folder="auc", suffix, doplot=F, writesql=F, correlation=T, removeDiag=F)
        rocs[[length(rocs)+1]] = roc
    }
    names(rocs) = suffixes
    roro[[length(roro)+1]] = rocs
}
save(roro, file="auc_prediction_human_roro.rda")








