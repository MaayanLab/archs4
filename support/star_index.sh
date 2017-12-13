
STAR_INDEX="/Users/maayanlab/data/starindex/mouse_ensemble"

STAR_INDEX="/Users/maayanlab/data/starindex/human_ensemble_90"

GENOME="/Users/maayanlab/data/genomes/Homo_sapiens.GRCh38.dna.chromosome.fa"
GTF="/Users/maayanlab/data/genomes/Homo_sapiens.GRCh38.90.gtf"

~/OneDrive/star/STAR \
--runMode genomeGenerate \
--genomeDir $STAR_INDEX \
--genomeFastaFiles $GENOME \
--sjdbGTFfile $GTF \
--runThreadN 8 \
--genomeSAsparseD 2 \
--genomeSAindexNbases 13



STAR_INDEX="/Users/maayanlab/data/starindex/mouse_ensemble_90"

GENOME="/Users/maayanlab/data/genomes/Mus_musculus.GRCm38.dna.chromosome.fa"
GTF="/Users/maayanlab/data/genomes/Mus_musculus.GRCm38.90.gtf"

~/OneDrive/star/STAR \
--runMode genomeGenerate \
--genomeDir $STAR_INDEX \
--genomeFastaFiles $GENOME \
--sjdbGTFfile $GTF \
--runThreadN 8 \
--genomeSAsparseD 2 \
--genomeSAindexNbases 13



