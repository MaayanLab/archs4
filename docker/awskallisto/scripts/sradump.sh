#!/bin/bash
echo "Start fastq dump"
echo "----------------------------------------------"

SRAbase="$1"

SRAfolder="/alignment/data/uploads"
fastqout="/alignment/data/uploads"
SRAtool="/alignment/tools/sratools/bin/fastq-dump"

mkdir -p $fastqout


echo "Extract SRA files"
echo "----------------------------------------------"


echo "Dumping..."

x=$( $SRAtool -I -X 1 -Z --split-spot "$SRAfolder/$SRAbase" | wc -l )

if [ "$x" == "8" ]; then 
  echo "$SRAfolder/$SRAbase contains paired-end sequencing data, dumping..."
  ## dumping paired reads files in paired_fastq
  $SRAtool -I --split-files -O $fastqout $SRAfolder/$SRAbase
  paired=1
else
  echo "$SRAfolder/$SRAbase contains single sequencing data, dumping..."
  ## dumbing single reads in fastq
  $SRAtool -O $fastqout $SRAfolder/$SRAbase
  paired=0
fi
echo "-------------------- Done -----------------------"


rm "$SRAfolder/$SRAbase"