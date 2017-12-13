#!/usr/bin/python
import datetime, time
import subprocess
import shlex
import os
import os.path
import sys
import tinys3
import glob
import urllib2
import urllib
import boto
from boto.s3.key import Key
import requests
import json


awsid = os.environ['AWSID']
awskey = os.environ['AWSKEY']

def uploadS3(file, key, bucket):
    conn = tinys3.Connection(awsid, awskey, tls=True)
    f = open(file,'rb')
    conn.upload(key, f, bucket)

def basename(p):
    temp = p.split("/")
    return temp[len(temp)-1]

r = requests.get("https://amp.pharm.mssm.edu/awsscheduler/getjob.php?mode=STAR")
jj = r.json()

if jj['id'] != "empty":
    if str(jj['type']) == "sequencing":
        links = jj['datalinks'].split(";")
        organism = str(jj['parameters']).split(":")[1]
        
    index = "/alignment/data/index/"+organism+"/Genome"
    indexlink = "https://s3.amazonaws.com/mssm-seq-index-star-"+organism+"/Genome"
    if not os.path.isfile(index):
        print("Load index file")
        urllib.urlretrieve(indexlink, index)
    index = "/alignment/data/index/"+organism+"/transcriptInfo.tab"
    indexlink = "https://s3.amazonaws.com/mssm-seq-index-star-"+organism+"/transcriptInfo.tab"
    if not os.path.isfile(index):
        print("Load index file")
        urllib.urlretrieve(indexlink, index)
    
    for ll in links:
        ll = str(ll)
        fb = basename(ll)
        
        urllib.urlretrieve(ll, "/alignment/data/uploads/"+fb)
        
        if fb.endswith(".sra"):
            print "download done, do SRA dump..."
            subprocess.call(shlex.split('./scripts/sradump.sh '+fb))
        if fb.endswith(".gz"):
            os.chdir("/alignment/data/uploads")
            subprocess.call(shlex.split("gunzip "+fb))
            os.chdir("/alignment")
        
        filenames = next(os.walk("/alignment/data/uploads"))[2]
        index = "/alignment/data/index/"+organism
        
        if len(filenames) == 2:
            with open("/alignment/data/results/runinfo.txt", "w") as f:
                print("STAR --genomeDir "+index+" --limitBAMsortRAM 10000000000 --runThreadN 8 --quantMode GeneCounts --outSAMstrandField intronMotif --outFilterIntronMotifs RemoveNoncanonical --outFileNamePrefix /alignment/data/results/ --readFilesIn /alignment/data/uploads/"+filenames[1]+" --outSAMtype BAM SortedByCoordinate --outReadsUnmapped Fastx --outSAMmode Full --limitIObufferSize 50000000")
                subprocess.call(shlex.split("STAR --genomeDir "+index+" --limitBAMsortRAM 10000000000 --runThreadN 8 --quantMode GeneCounts --outSAMstrandField intronMotif --outFilterIntronMotifs RemoveNoncanonical --outFileNamePrefix /alignment/data/results/ --readFilesIn /alignment/data/uploads/"+filenames[1]+" --outSAMtype BAM SortedByCoordinate --outReadsUnmapped Fastx --outSAMmode Full --limitIObufferSize 50000000"), stderr=f)
        
        if len(filenames) == 3:
            with open("/alignment/data/results/runinfo.txt", "w") as f:
                subprocess.call(shlex.split("STAR --genomeDir "+index+" --limitBAMsortRAM 10000000000 --runThreadN 8 --quantMode GeneCounts --outSAMstrandField intronMotif --outFilterIntronMotifs RemoveNoncanonical --outFileNamePrefix /alignment/data/results/ --readFilesIn /alignment/data/uploads/"+filenames[1]+" /alignment/data/uploads/"+filenames[2]+" --outSAMtype BAM SortedByCoordinate --outReadsUnmapped Fastx --outSAMmode Full --limitIObufferSize 50000000"), stderr=f)
        
        print("STAR alignment completed")
        
        uploadS3("/alignment/data/results/ReadsPerGene.out.tab", str(jj['id'])+"-"+str(jj['uid'])+"_star.tsv", "mssm-seq-results-star")
        
        print("Uploaded raw counts to S3")
        
        mapping = "/alignment/data/mapping/star_map_"+organism+".rda"
        
        if not os.path.isfile(mapping):
            print("Load gene mapping information")
            mappinglink = "https://s3.amazonaws.com/mssm-seq-genemapping-star/star_map_"+organism+".rda"
            urllib.urlretrieve(mappinglink, mapping)
        
        subprocess.call(shlex.split("Rscript --vanilla scripts/genelevel.r "+mapping))
        uploadS3("/alignment/data/results/gene_abundance.tsv", str(jj['id'])+"-"+str(jj['uid'])+"_star_gene.tsv", "mssm-seq-generesults-star")
        #uploadS3("/alignment/data/results/runinfo.txt", str(jj['id'])+"-"+str(jj['uid'])+"_kallisto_info.tsv", "mssm-seq-qc")
        
        print("Uploaded raw gene counts to S3")
        
        genesymbols = list()
        values = list()
        with open('/alignment/data/results/gene_abundance.tsv') as f:
            lines = f.readlines()
            for l in lines:
                sp = l.strip().split("\t")
                genesymbols.append(sp[0]);
                values.append(sp[1]);
        
        numreads = 0
        numaligned = 0
        estimatedlength = 0
        with open('/alignment/data/results/Log.final.out') as f:
            lines = f.readlines()
            for l in lines:
                if "Number of input reads" in l:
                    numreads = l.strip().split(" |\t")[1]
                if "Uniquely mapped reads number" in l:
                    numaligned = l.strip().split(" |\t")[1]
                if "Average input read length" in l:
                    estimatedlength = l.strip().split(" |\t")[1]
        
        sample = {
            'id': str(jj['id']),
            'uid': str(jj['uid']),
            'nreads': numreads,
            'naligned': numaligned,
            'nlength': estimatedlength,
            'genesymbols': genesymbols,
            'values': values
        }
        
        #send kallisto quantification to database
        r = requests.post('http://amp.pharm.mssm.edu/awsscheduler/uploadgenecount.php?mode=STAR', json=sample)
        print("Sent counts to database")
        # clean up after yourself, a bit risky if python fails files fill up container space
        files = glob.glob('/alignment/data/results/*')
        for f in files:
            os.remove(f)
        
        files = glob.glob('/alignment/data/uploads/*')
        for f in files:
            os.remove(f)
