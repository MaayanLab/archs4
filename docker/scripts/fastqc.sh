#!/bin/bash
echo "Start FastQC"
echo "----------------------------------------------"

file=$1

fastqc="/alignment/tools/fastqc/fastqc"

fastqfolder="/alignment/data/uploads/fastq/"
fastqcfolder="/alignment/data/results/fastQC/"

mkdir -p $fastqcfolder

$fastqc $fastqfolder/$file -t 8 -o $fastqcfolder
