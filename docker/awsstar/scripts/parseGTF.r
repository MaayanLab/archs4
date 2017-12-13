setwd("~/data/sequences")

tt = read.table("~/data/genomes/Homo_sapiens.GRCh38.87.gtf", quote="", comment.char="#", sep="\t", stringsAsFactors=F)

tt = read.table("~/data/genomes/Mus_musculus.GRCm38.88.gtf", quote="", comment.char="#", sep="\t", stringsAsFactors=F)

gg = which(tt[,3] == "gene")

tts = tt[gg,]

li = list()

for(i in 1:nrow(tts)){
    sp = unlist(strsplit(tts[i,9],"; "))
    s1 = gsub("\"", "", sp[1])
    s1 = gsub("gene_id ", "", s1)

    s2 = gsub("\"", "", sp[3])
    s2 = gsub("gene_name ", "", s2)
    
    li[[length(li)+1]] = c(s1,s2)

}

dd = do.call(rbind, li)

d = dd[,2]
names(d) = dd[,1]
save(d, file="star_map_mouse.rda")


ttt = read.table("~/data/testing/out1/t1ReadsPerGene.out.tab", quote="", comment.char="#", sep="\t", stringsAsFactors=F, skip=4)

t4 = ttt[,2]

names(t4) = d[ttt[,1]]
write.table(cbind(d[ttt[,1]], t4), file="geneexpression.tsv", quote=F, col.names=F, row.names=F, sep="\t")


8350
f3d81dc924
seq-results
ftp://ftp-trace.ncbi.nih.gov/sra/sra-instant/reads/ByRun/sra/SRR/SRR179/SRR179745/SRR179745.sra
organism:human
waiting


/alignment/tools/star/STAR --genomeDir /alignment/data/index/human --limitBAMsortRAM 10000000000 --runThreadN 8 --quantMode GeneCounts --outSAMstrandField intronMotif --outFilterIntronMotifs RemoveNoncanonical --outFileNamePrefix /alignment/data/results/ --readFilesIn /alignment/data/uploads/SRR179745.fastq --outSAMtype BAM SortedByCoordinate --outReadsUnmapped Fastx --outSAMmode Full --limitIObufferSize 50000000



