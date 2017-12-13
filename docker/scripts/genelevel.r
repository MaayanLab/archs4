
library("plyr")

args <- commandArgs(TRUE)

mapping = args[1]
res = load(mapping)

f = "/alignment/data/results/abundance.tsv"

abu = read.table(f, sep="\t", stringsAsFactors=F)
ugene = cb[,2]

m3 = match(abu[,1], cb[,1])
cco = cbind(abu,ugene[m3])[-1,]
co = cco[,c(6,4)]
co[,1] = as.character(co[,1])
df = data.frame(co[,1], as.numeric(co[,2]))
colnames(df) = c("gene", "value")

dd = ddply(df,.(gene),summarize,sum=sum(value),number=length(gene))
ge = dd[,2]
names(ge) = dd[,1]

write.table(ge, file="/alignment/data/results/gene_abundance.tsv", quote=F, col.names=F, sep="\t")

