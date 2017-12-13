setwd("~/OneDrive/archs4final")

library("RMySQL")
library("SRAdb")
library("rhdf5")
library("GEOquery")
library("RColorBrewer")
library("preprocessCore")


file= "mouse_matrix.h5"
genes = h5read(file, "meta/genes")

mexp = h5read(file, "data/expression", index=list(1:length(genes), sample(1:50000, 1000)))
rownames(mexp) = genes


file= "human_matrix.h5"
genes = h5read(file, "meta/genes")

hexp = h5read(file, "data/expression", index=list(1:length(genes), sample(1:50000, 1000)))
rownames(hexp) = genes

inter = intersect(rownames(hexp), rownames(mexp))

hexp = hexp[inter,]
hexp = normalize.quantiles(hexp)
mexp = mexp[inter,]
mexp = normalize.quantiles(mexp)
rownames(hexp) = inter
rownames(mexp) = inter


igenes = inter[sample(1:length(inter), 1000)]

cm = cor(t(mexp[igenes,]))
ch = cor(t(hexp[igenes,]))

diag(cm) = NA
diag(ch) = NA

plot(cm[1,], ch[200,], pch=20)

call = cor(t(cm), t(cm), use = "pairwise.complete.obs")
plot(density(call), lwd=4)

call = cor(cm[1,], t(ch), use = "pairwise.complete.obs")
plot(call[,1], pch=20)

wp = which(call < 0)
plot(density(c(call[wp], abs(call[wp]))), lwd=3, col="red")
lines(density(call), lwd=3)
abline(v=0, col="grey")

call = cor(t(cm), t(ch), use = "pairwise.complete.obs")
plot(density(diag(call)), lwd=3)





file= "human_hiseq_int1.0.h5"
genes = h5read(file, "meta/genes")

nhexp = h5read(file, "data/expression", index=list(1:length(genes), sample(1:65000, 1000)))
nhexp = normalize.quantiles(nhexp)
rownames(nhexp) = genes

nhexp = nhexp[inter, ]

cnh = cor(t(nhexp[igenes,]))
diag(cnh) = NA
call = cor(t(ch), t(cnh), use = "pairwise.complete.obs")

plot(density(diag(call)), lwd=3)













