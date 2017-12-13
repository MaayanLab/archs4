gtf = read.table("~/data/genomes/Homo_sapiens.GRCh38.90.gtf", sep="\t", stringsAsFactor=F)

ww = which(gtf[,3] == "transcript")
gtf = gtf[ww,]

sp = strsplit(gtf[,9], "; ")

mapping = list()

for(s in sp){
    geneid = gsub("gene_id ","",s[1])
    transid = gsub("transcript_id ","",s[3])
    genesym = gsub("gene_name ","",s[5])
    
    cc = c(geneid, genesym, transid)
    mapping[[length(mapping)+1]] = cc
}

mapping = do.call(rbind, mapping)
colnames(mapping) = c("gene_id", "gene_symbol", "transcript_id")

save(mapping, file="mapping_human_ensembl_90.rda")


gtf = read.table("~/data/genomes/Mus_musculus.GRCm38.90.gtf", sep="\t", stringsAsFactor=F)

ww = which(gtf[,3] == "transcript")
gtf = gtf[ww,]

sp = strsplit(gtf[,9], "; ")

mapping = list()

for(s in sp){
    geneid = gsub("gene_id ","",s[1])
    transid = gsub("transcript_id ","",s[3])
    genesym = gsub("gene_name ","",s[5])
    
    cc = c(geneid, genesym, transid)
    mapping[[length(mapping)+1]] = cc
}

mapping = do.call(rbind, mapping)
colnames(mapping) = c("gene_id", "gene_symbol", "transcript_id")

save(mapping, file="mapping_mouse_ensembl_90.rda")


#------------------------------------

load("mapping_human_ensembl_90.rda")
an = read.table("Homo_sapiens.GRCh38.90_annotation.txt", sep="\t", stringsAsFactor=F)

mm = match(an[,1], mapping[,3])
gs = mapping[mm,2]
an2 = cbind(gs, an)

write.table(an2, file="Homo_sapiens.GRCh38.90_annotation_fix.txt", quote=F, col.names=F, row.names=F, sep="\t")


load("mapping_mouse_ensembl_90.rda")
an = read.table("Mus_musculus.GRCm38.90_annotation.txt", sep="\t", stringsAsFactor=F)

mm = match(an[,1], mapping[,3])
gs = mapping[mm,2]
an2 = cbind(gs, an)

write.table(an2, file="Mus_musculus.GRCm38.90_annotation_fix.txt", quote=F, col.names=F, row.names=F, sep="\t")

















