
args <- commandArgs(TRUE)

mapping = args[1]
res = load(mapping)

ttt = read.table("/alignment/data/results/ReadsPerGene.out.tab", quote="", comment.char="#", sep="\t", stringsAsFactors=F, skip=4)
t4 = ttt[,2]
names(t4) = d[ttt[,1]]
write.table(cbind(d[ttt[,1]], t4), file="/alignment/data/results/gene_abundance.tsv", quote=F, col.names=F, row.names=F, sep="\t")


