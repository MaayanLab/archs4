setwd("~/OneDrive/archs4final")

library("RMySQL")
library("SRAdb")
library("digest")
library("GEOquery")
library("RColorBrewer")
library("plyr")
library("preprocessCore")

cols <- brewer.pal(8,"Set1")

getROC <- function(exp, gmtf, polish){
    gmt = readLines(gmtf)
    genes = rownames(exp)
    go = list()
    for(ll in gmt){
        sp = unlist(strsplit(ll, "\t"))
        gene = sp[1]
        t = c()
        for(i in 3:length(sp)){
            sp1 = unlist(strsplit( sp[i], ","))
            t = c(t, sp1[1])
        }
        go[[length(go)+1]] = intersect(genes, t)
        names(go)[length(go)] = gsub("_$", "", gene)
    }
    uu = unlist(go)
    ta = names(rev(sort(table(uu))))
    
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

    cc = cor(t(expt[ta,]), t(expt[ta, ]))
    ww = which(cc > 0.99)
    if(polish){
        cc = medpolish(cc)$residuals
    }
    cc[ww] = NA

    ranki = list()
    sul = list()
    avga = c()

    fnames = c()
    for(t in names(go)){
        termg = intersect(go[[t]], rownames(cc))
        if(length(termg) > 1){
            s = colMeans(cc[go[[t]],], na.rm = T)
            sul[[length(sul)+1]] = s
            fnames=  c(fnames, t)
        }
    }
    sus = do.call(cbind, sul)
    colnames(sus) = fnames
    re = scale(sus)
    re[is.na(re)] = 0

    rocs = c()
    counter = 0

    for(gen in names(geneo)){
        if(length(geneo[[gen]]) > 6){
            counter = counter+1
            su = names(rev(sort(re[gen,])))
            le = cumsum(su %in% geneo[[gen]])

            rocs = c(rocs, sum(le/max(le))/length(le))
            names(rocs)[length(rocs)] = gen
        }
    }
    return(rocs)
}



mydb = dbConnect(MySQL(), user='xxx', password='zzz', dbname='alignment_pipeline', host='yyy.rds.amazonaws.com')



sql = paste0("SELECT gsm, sra, listid, gse FROM samplemapping")
rs = dbSendQuery(mydb, sql)
mapping = fetch(rs, n=-1)


sql = paste0("SELECT id, uid, datalinks FROM star_sequencing")
rs = dbSendQuery(mydb, sql)
dd = fetch(rs, n=-1)


srr = gsub("ftp://ftp-trace.ncbi.nih.gov/sra/sra-instant/reads/ByRun/sra/SRR/SRR[0-9]{3}/SRR[0-9]+/","",dd[,3])
srr = gsub("\\.sra", "", srr)

ll = list()

for(i in 1:nrow(dd)){
    print(i)
    result = tryCatch({
        pp = paste0("https://s3.amazonaws.com/mssm-seq-generesults-star/",dd[i,1], "-", dd[i,2], "_star_gene.tsv")
        download.file(pp, "temp.tmp", quiet = FALSE)
        tt = read.table("temp.tmp", stringsAsFactor=F, sep="\t")
        
        df = data.frame(tt)
        colnames(df) = c("gene", "value")
        ddo = ddply(df,.(gene),summarize,sum=sum(value),number=length(gene))
        d1 = ddo[,2]
        names(d1) = ddo[,1]
        ll[[length(ll)+1]] = d1
        names(ll)[length(ll)] = srr[i]
        
    }, warning = function(w) {
    }, error = function(e) {
    }, finally = {
    });
}

rl = unlist(lapply(ll, length))
ww = which(rl == rl[1])
star_cc = do.call(cbind, ll[ww])
save(star_cc, file="star_cc.rda")

sras = colnames(star_cc)
ww = which(mapping[,2] %in% sras)


mydb = dbConnect(MySQL(), user='kallistomaster', password='Tuhuratha3', dbname='alignment_pipeline', host='kallisto.ckjqvk8k3pqb.us-east-1.rds.amazonaws.com')

kallisto_ll = list()
for(lid in mapping[ww,3]){
    sql = paste0("SELECT DISTINCT genemapping.genesymbol AS genesymbol, kallistoquant.value AS value FROM kallistoquant INNER JOIN genemapping ON kallistoquant.geneid=genemapping.geneid  WHERE listid=",lid)
    rs = dbSendQuery(mydb, sql)
    sig = fetch(rs, n=-1)
    kallisto_ll[[length(kallisto_ll)+1]] = sig[,2]
}
rl = unlist(lapply(kallisto_ll, length))
www = which(rl == rl[1])
kallisto_cc = do.call(cbind, kallisto_ll[www])
rownames(kallisto_cc) = sig[,1]
colnames(kallisto_cc) = mapping[ww,2][www]

cinter = intersect(colnames(star_cc), colnames(kallisto_cc))
rinter = intersect(rownames(star_cc), rownames(kallisto_cc))

star = star_cc[rinter,cinter]
kallisto = kallisto_cc[rinter,cinter]

load("star_kallisto_counts.rda")
rinter = rownames(star)
cinter = colnames(star)
star = normalize.quantiles(star)
kallisto = normalize.quantiles(kallisto)
rownames(star) = rinter
colnames(star) = cinter

rownames(kallisto) = rinter
colnames(kallisto) = cinter

star = log2(star+1)
kallisto = log2(kallisto+1)


coco = cor(star, kallisto)
plot(density(diag(coco)), xlim=c(0,1), lwd=3)
lines(density(coco[upper.tri(coco)]), col=2, lwd=3)


gses = unique(mapping[which(mapping[,2] %in% cinter),4])

star_concordance = c()
for(gse in gses){
    ww = intersect(mapping[mapping[,4] == gse,2], cinter)
    if(length(ww) > 1){
        co = cor(star[,ww])
        print(mean(co))
        star_concordance = c(star_concordance, mean(co))
    }
}

kallisto_concordance = c()
for(gse in gses){
    ww = intersect(mapping[mapping[,4] == gse,2], cinter)
    if(length(ww) > 1){
        co = cor(kallisto[,ww])
        print(mean(co))
        kallisto_concordance = c(kallisto_concordance, mean(co))
    }
}

print("if negative kallisto has better concordance")
sum(star_concordance - kallisto_concordance)


rs = rowSums(star)
rk = rowSums(kallisto)


cl = cor(t(star[1:1000,]), t(kallisto[1:1000,]))

plot(density(diag(cl)))

star2 = matrix(0, nrow(star), ncol(star))
ww = which(star > 1)
star2[ww] = 1
sr = colSums(star2)


kallisto2 = matrix(0, nrow(kallisto), ncol(kallisto))
ww = which(kallisto > 1)
kallisto2[ww] = 1
kr = colSums(kallisto2)

wh = which(sr > 25000)

pdf("detected_genes.pdf")
plot(density(kr[-wh]), lwd=3, xlab="genes detected / sample")
lines(density(sr[-wh]), col=2, lwd=3)
legend("topleft", legend=c("STAR","Kallisto"), col=c(2,1), lty=1, lwd=4)
dev.off()







