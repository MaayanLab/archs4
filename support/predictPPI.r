library("rhdf5")
library("Rtsne")
library("mygene")
library("polyclip")
library("preprocessCore")
setwd("~/OneDrive/archs4final")

range01 <- function(x){(x-min(x))/(max(x)-min(x))}

res = load("clean_ccle.rda")

res = load("clean_2000_gtex.rda")

res = load("clean_archs4_human.rda")
human = mexp

res = load("clean_archs4_mouse.rda")
mouse = mexp

expt = normalize.quantiles(mouse)
rownames(expt) = rownames(mouse)
mouse = expt

expt = normalize.quantiles(human)
rownames(expt) = rownames(human)
human = expt

expt = normalize.quantiles(gtex)
rownames(expt) = rownames(gtex)
gtex = expt

expt = normalize.quantiles(ccle)
rownames(expt) = rownames(ccle)
ccle = expt

exps = list(mouse, human, ccle, gtex)
names(exps) = c("ARCHS4_mouse", "ARCHS4_human", "CCLE", "GTEX")

ppis = c("HuMAp_ppi_2017_06_07","biogrid_ppi_2017_06_09","bioplex_ppi_2017_06_07")
names(ppis) = c("HuMAp","Biogrid","Bioplex")

aucsList = list()

for(i in 1:length(exps)[1]){
    
    for(j in 1:length(ppis)){
        
        exp = exps[[i]]
        
        ppi_data = ppis[[j]]
        sig = read.table(paste0("ppi/",ppi_data,".sig"), sep="\t", stringsAsFactors=F)[,c(1,6)]
        rr = rev(sort(table(unlist(c(unlist(sig[,1]), unlist(sig[2]))))))
        
        ww = which(rr >= 5)
        pgenes = names(rr)[ww]

        inter = intersect(rownames(exp), names(rr))
        exp = exp[inter,]
        pgenes = intersect(inter, pgenes)

        ppi_genesets = list()
        for(g in pgenes){
            w1 = which(sig[,1] == g)
            i1 = sig[w1,2]
            w2 = which(sig[,2] == g)
            i2 = sig[w2,1]
            ui = unique(c(i1, i2))
            ui = intersect(ui, inter)
            
            if(length(ui) >= 5){
                ppi_genesets[[length(ppi_genesets)+1]] = ui
                names(ppi_genesets)[length(ppi_genesets)] = g
            }
        }
        pgenes = names(ppi_genesets)
            
        correlation_matrix = cor(t(exp[pgenes,]), t(exp))
        #sign_matrix = sign(correlation_matrix)
        #correlation_matrix = (correlation_matrix*correlation_matrix)*sign_matrix
        
        correlation_matrix[correlation_matrix > 0.95] = NA
        
        mean_matrix = matrix(0, length(pgenes), length(ppi_genesets))
        rownames(mean_matrix) = pgenes
        colnames(mean_matrix) = names(ppi_genesets)
        
        for(p in names(ppi_genesets)){
            mean_matrix[, p] = rowMeans(correlation_matrix[,ppi_genesets[[p]]], na.rm=T)
        }
        
        aucs = c()
        for(p in pgenes){
            cu = rep(0, ncol(mean_matrix))
            names(cu) = names(rev(sort(mean_matrix[p,])))
            
            ig = intersect(ppi_genesets[[p]],names(cu))
            
            if(length(ig) >= 5){
                cu[ig] = 1
                cs = cumsum(cu)
                
                x = 1:length(cs)
                y = cs
                auc = trapz(range01(x),range01(y))
                
                #plot(range01(1:length(cs)), range01(cs), type="l", xlim=c(0,1), ylim=c(0,1), lwd=3, xlab="1 − specificity", ylab="Sensitivity", main=paste(p, length(ppi_genesets[[p]])))
                #grid()
                #abline(0,1, col="red", lty=2, lwd=2)
                #legend("bottomright", legend=c(paste("AUC =", format(sum(cs/max(cs))/length(cs), digits=3))), bty = "n", cex=1.4)
                
                aucs = c(aucs, auc)
            }
        }
        
        plot(density(aucs), lwd=3, main=paste(names(exps)[i], names(ppis)[j], mean(aucs)))
        aucsList[[length(aucsList)+1]] = aucs
    }
}

colo = colorRampPalette(brewer.pal(3,"Spectral"))(3)

pdf("ppi_prediction_ww.pdf", 8, 5)
vioplot2(aucsList, col=c(colo, colo, colo, colo), names=rep("", length(aucsList)))
abline(v=3.5, col="grey")
abline(v=6.5, col="grey")
abline(v=9.5, col="grey")
text(2, 1.05, "ARCHS4 mouse")
text(5, 1.05, "ARCHS4 human")
text(8, 1.05, "CCLE")
text(11, 1.05, "GTEX")
legend("bottomright", legend=c("Hu.map", "Biogrid", "Bioplex"), fill=colo, bg="white")
mtext("AUC", side=2, line=2.5, cex=1.4)
dev.off()



aucsList = list()

for(kk in 21:30){
    i = 1
    j = 3
    
    exp = exps[[i]]
    
    ppi_data = ppis[[j]]
    sig = read.table(paste0("ppi/",ppi_data,".sig"), sep="\t", stringsAsFactors=F)[,c(1,6)]
    rr = rev(sort(table(unlist(c(unlist(sig[,1]), unlist(sig[2]))))))
    
    ww = which(rr > kk)
    pgenes = names(rr)[ww]
    print(length(pgenes))

    inter = intersect(rownames(exp), names(rr))
    exp = exp[inter,]
    pgenes = intersect(inter, pgenes)

    ppi_genesets = list()
    for(g in pgenes){
        w1 = which(sig[,1] == g)
        i1 = sig[w1,2]
        w2 = which(sig[,2] == g)
        i2 = sig[w2,1]
        ui = unique(c(i1, i2))
        ui = intersect(ui, inter)
        
        if(length(ui) >= kk){
            ppi_genesets[[length(ppi_genesets)+1]] = ui
            names(ppi_genesets)[length(ppi_genesets)] = g
        }
    }
    pgenes = names(ppi_genesets)
    
    correlation_matrix = cor(t(exp[pgenes,]), t(exp))
    correlation_matrix[correlation_matrix > 0.99] = NA
    
    mean_matrix = matrix(0, length(pgenes), length(ppi_genesets))
    rownames(mean_matrix) = pgenes
    colnames(mean_matrix) = names(ppi_genesets)
    
    for(p in pgenes){
        mean_matrix[, p] = rowMeans(correlation_matrix[,ppi_genesets[[p]]], na.rm=T)
    }
    
    aucs = c()
    for(p in pgenes){
        cu = rep(0, ncol(mean_matrix))
        names(cu) = names(rev(sort(mean_matrix[p,])))
        
        ig = intersect(ppi_genesets[[p]],names(cu))
        
        if(length(ig) > 10){
            print(length(ig))
            cu[ig] = 1
            cs = cumsum(cu)
            
            x = 1:length(cs)
            y = cs
            auc = trapz(range01(x),range01(y))
            
            #plot(range01(1:length(cs)), range01(cs), type="l", xlim=c(0,1), ylim=c(0,1), lwd=3, xlab="1 − specificity", ylab="Sensitivity", main=paste(p, length(ppi_genesets[[p]])))
            #grid()
            #abline(0,1, col="red", lty=2, lwd=2)
            #legend("bottomright", legend=c(paste("AUC =", format(sum(cs/max(cs))/length(cs), digits=3))), bty = "n", cex=1.4)
            
            aucs = c(aucs, auc)
        }
    }
    
    plot(density(aucs), lwd=3, main=paste(names(exps)[i], names(ppis)[j], mean(aucs)))
    aucsList[[length(aucsList)+1]] = aucs

}


colo = colorRampPalette(brewer.pal(5,"Spectral"))(16)


pdf("bioplex_neighborhoodsize.pdf")
vioplot2(aucsList, col=colo, names=5:30)
mtext("bioplex", side=3, line=2)
mtext("min PPI neighborhood size", side=1, line=3)
mtext("AUC", side=2, line=3)
dev.off()


sgl = list()

for(j in 1:length(ppis)){
    
    ppi_data = ppis[[j]]
    sig = read.table(paste0("ppi/",ppi_data,".sig"), sep="\t", stringsAsFactors=F)[,c(1,6)]
    
    edges = c()
    
    for(i in 1:nrow(sig)){
        if(sig[i,1] < sig[i,2]){
            edges = c(edges, paste(sig[i,1], sig[i,2]))
        }
        else{
            edges = c(edges, paste(sig[i,2], sig[i,1]))
        }
    }
    sgl[[length(sgl)+1]] = unique(edges)
}


i1 = sgl[[1]]
i2 = sgl[[2]]
i3 = sgl[[3]]

i12 = intersect(sgl[[1]], sgl[[2]])
i13 = intersect(sgl[[1]], sgl[[3]])
i23 = intersect(sgl[[2]], sgl[[3]])
i123 = intersect(i3, i1)

iall = unique(unlist(sgl))

pdf("ppi_overlap.pdf")
vp = draw.triple.venn(length(sgl[[1]]), length(sgl[[2]]), length(sgl[[3]]), length(i12), length(i23), length(i13), length(i123), category=c("hu.map","Biogrid","Bioplex"),  cat.cex = 1.4, cex=1.4)
dev.off()

str(vp)
grid.ls()

ix <- sapply(vp, function(x) grepl("text", x$name, fixed = TRUE))
labs <- do.call(rbind.data.frame, lapply(vp[ix], `[`, c("x", "y", "label")))

colo = colorRampPalette(brewer.pal(5,"Spectral"))(4)

A <- list(list(x = as.vector(vp[[3]][[1]]), y = as.vector(vp[[3]][[2]])))
B <- list(list(x = as.vector(vp[[4]][[1]]), y = as.vector(vp[[4]][[2]])))
C <- list(list(x = as.vector(vp[[5]][[1]]), y = as.vector(vp[[5]][[2]])))

AintB <- polyclip(A, B)
AintC <- polyclip(A, C)
BintC <- polyclip(B, C)

ABC <- polyclip(AintB, C)
AB <- polyclip(AintB, C, op="minus")
AC <- polyclip(AintC, B, op="minus")
BC <- polyclip(BintC, A, op="minus")

pdf("ppi_overlap_all2.pdf")
plot(c(0, 1), c(0, 1), type = "n", axes = FALSE, xlab = "", ylab = "")
polygon(ABC[[1]], col = colo[1])
polygon(AB[[1]], col = colo[2])
polygon(AC[[1]], col = colo[3])
polygon(BC[[1]], col = colo[4])
polygon(A[[1]])
polygon(B[[1]])
polygon(C[[1]])
text(x = labs$x, y = labs$y-0.01, labels = labs$label, cex=1.4)
text(x = labs$x, y = labs$y+0.05, labels = c("", "II", "", "IV", "I", "III", "", "", "",""), cex=2)
dev.off()



i = 1
kk = 5
exp = exps[[i]]

sig = strsplit(iall, " ")
sig = do.call(rbind, sig)

rr = rev(sort(table(unlist(c(sig[,1], sig[,2])))))

pgenes = names(rr)
print(length(pgenes))

inter = intersect(rownames(exp), names(rr))
exp = exp[inter,]
pgenes = inter

correlation_matrix = cor(t(exp))
correlation_matrix[correlation_matrix > 0.99] = NA

s1 = which(sig[,1] %in% pgenes & sig[,2] %in% pgenes)
length(s1)
sig = sig[s1,]


r12 = strsplit(sgl[[1]], " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]



r12 = strsplit(setdiff(i12, i3), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
plot(density(correlation_matrix[r12], na.rm=T), lwd=3, col=2)
c12 = correlation_matrix[r12]

r12 = strsplit(setdiff(i23, i1), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col=3)
c23 = correlation_matrix[r12]

r12 = strsplit(setdiff(i13, i2), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col=4)
c13 = correlation_matrix[r12]

r12 = strsplit(i123, " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col=5)
call = correlation_matrix[r12]

r12 = strsplit(setdiff(setdiff(sgl[[1]], sgl[[2]]), sgl[[3]]), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col="red")
e1 = r12
c1 = correlation_matrix[r12]

r12 = strsplit(setdiff(setdiff(sgl[[2]], sgl[[1]]), sgl[[3]]), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col="green")
e2 = r12
c2 = correlation_matrix[r12]

r12 = strsplit(setdiff(setdiff(sgl[[3]], sgl[[1]]), sgl[[2]]), " ")
r12 = do.call(rbind, r12)
r12 = r12[which(r12[,1] %in% pgenes & r12[,2] %in% pgenes),]
lines(density(correlation_matrix[r12], na.rm=T), lwd=3, col="blue")
e3 = r12
c3 = correlation_matrix[r12]


ll = list(c1, c2, c3, call, c12, c23, c13)
ll = lapply(ll, na.omit)
q75 = do.call(rbind, lapply(ll, quantile))[,4]

colo = colorRampPalette(brewer.pal(5,"Spectral"))(4)
colo = colo[c(1,4,3,2)]
q75t = c(q75[1:3], NA, q75[4:length(q75)])
names(q75t) = c("hu.map", "Biogrid","Bioplex","" ,"I","II","III","IV")

pdf("quantile_correlation.pdf")
barplot(q75t, c(3, 3, 3, 2, 2,2,2,2), col=c("#dddddd","#dddddd","#dddddd", "#ffffff",colo), las=2, ylab="75% quantile correlation", cex.lab=1.4, cex.axis=1, cex.names=1.4, ylim=c(0,0.25))
abline(h=0, lwd=2)
abline(v=12)
dev.off()




pdf("ppi_complete.pdf", 10,6)
colo = colorRampPalette(brewer.pal(5,"Spectral"))(4)


layout(matrix(c(1,1,2,3), 2, 2, byrow = F), widths=c(2,2), heights=c(2,2))

par(mar=c(5,1,5,3))

plot(c(0, 1), c(0, 1), type = "n", axes = FALSE, xlab = "", ylab = "")
polygon(ABC[[1]], col = colo[1])
polygon(AB[[1]], col = colo[2])
polygon(AC[[1]], col = colo[3])
polygon(BC[[1]], col = colo[4])
polygon(A[[1]])
polygon(B[[1]])
polygon(C[[1]])
text(x = labs$x, y = labs$y-0.01, labels = labs$label, cex=1.4)
text(x = labs$x, y = labs$y+0.05, labels = c("", "II", "", "IV", "I", "III", "", "", "",""), cex=2)

colo = colorRampPalette(brewer.pal(3,"Spectral"))(3)

par(mar=c(0,1.5,3,2))
vioplot2(aucsList, col=c(colo, colo, colo, colo), names=rep("", length(aucsList)))
abline(v=3.5, col="grey")
abline(v=6.5, col="grey")
abline(v=9.5, col="grey")
text(2, 1.05, "ARCHS4 mouse")
text(5, 1.05, "ARCHS4 human")
text(8, 1.05, "CCLE")
text(11, 1.05, "GTEX")
legend("bottomright", legend=c("Hu.map", "Biogrid", "Bioplex"), fill=colo, bg="white")
mtext("AUC", side=2, line=2.5, cex=1.4)


colo = colorRampPalette(brewer.pal(5,"Spectral"))(4)
colo = colo[c(1,4,3,2)]

par(mar=c(5,2,5,2))
barplot(q75t, c(3, 3, 3, 2, 2,2,2,2), col=c("#dddddd","#dddddd","#dddddd", "#ffffff",colo), las=2, ylab="75% quantile correlation", cex.lab=1.4, cex.axis=1, cex.names=1.4, ylim=c(0,0.25))
abline(h=0, lwd=2)
abline(v=12)
mtext("75% quantile correlation", side=2, line=3, cex=1.4)

dev.off()


lines(density(correlation_matrix, na.rm=T))

mean_matrix = matrix(0, length(pgenes), length(ppi_genesets))
rownames(mean_matrix) = pgenes
colnames(mean_matrix) = names(ppi_genesets)

for(p in pgenes){
    mean_matrix[, p] = rowMeans(correlation_matrix[,ppi_genesets[[p]]], na.rm=T)
}

aucs = c()
for(p in pgenes){
    cu = rep(0, ncol(mean_matrix))
    names(cu) = names(rev(sort(mean_matrix[p,])))
    
    ig = intersect(ppi_genesets[[p]],names(cu))
    
    if(length(ig) > 10){
        print(length(ig))
        cu[ig] = 1
        cs = cumsum(cu)
        
        x = 1:length(cs)
        y = cs
        auc = trapz(range01(x),range01(y))
        
        #plot(range01(1:length(cs)), range01(cs), type="l", xlim=c(0,1), ylim=c(0,1), lwd=3, xlab="1 − specificity", ylab="Sensitivity", main=paste(p, length(ppi_genesets[[p]])))
        #grid()
        #abline(0,1, col="red", lty=2, lwd=2)
        #legend("bottomright", legend=c(paste("AUC =", format(sum(cs/max(cs))/length(cs), digits=3))), bty = "n", cex=1.4)
        
        aucs = c(aucs, auc)
    }
}




