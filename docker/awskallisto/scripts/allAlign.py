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
from random import randint
from time import sleep
import time
from datetime import datetime

sleep(randint(0,40))

awsid = os.environ['AWSID']
awskey = os.environ['AWSKEY']

def uploadS3(file, key, bucket):
    conn = tinys3.Connection(awsid, awskey, tls=True)
    f = open(file,'rb')
    conn.upload(key, f, bucket)

def basename(p):
    temp = p.split("/")
    return temp[len(temp)-1]

files = glob.glob('/alignment/data/uploads/*')
for f in files:
    os.remove(f)

r = requests.get("http://amp.pharm.mssm.edu/awsscheduler/getjob.php?time="+str(randint(0,9000000000)))
jj = r.json()

if jj['id'] != "empty":
    if str(jj['type']) == "sequencing":
        links = jj['datalinks'].split(";")
        
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
        organism = str(jj['parameters']).split(":")[1]
        index = "/alignment/data/index/"+organism+"_index.idx"
        indexlink = "https://s3.amazonaws.com/mssm-seq-index/"+organism+"_index.idx"
        
        # load index if not already loaded
        if not os.path.isfile(index):
            print("Load index file")
            urllib.urlretrieve(indexlink, index)
        
        if len(filenames) == 1:
            with open("/alignment/data/results/runinfo.txt", "w") as f:
                subprocess.call(shlex.split("/alignment/tools/kallisto/kallisto quant -t 2 -i "+index+" --single -l 200 -s 20 -o /alignment/data/results /alignment/data/uploads/"+filenames[0]), stderr=f)
        
        if len(filenames) == 2:
            with open("/alignment/data/results/runinfo.txt", "w") as f:
                subprocess.call(shlex.split("/alignment/tools/kallisto/kallisto quant -t 2 -i "+index+" -o /alignment/data/results /alignment/data/uploads/"+filenames[0]+" /alignment/data/uploads/"+filenames[1]), stderr=f)
        
        print("Kallisto quantification completed")
        
        uploadS3("/alignment/data/results/abundance.tsv", str(jj['id'])+"-"+str(jj['uid'])+"_kallisto.tsv", "mssm-seq-results")
        
        print("Uploaded raw counts to S3")
        
        mapping = "/alignment/data/mapping/"+organism+"_mapping.rda"
        
        if not os.path.isfile(mapping):
            print("Load gene mapping information")
            mappinglink = "https://s3.amazonaws.com/mssm-seq-genemapping/"+organism+"_mapping.rda"
            urllib.urlretrieve(mappinglink, mapping)
        
        subprocess.call(shlex.split("Rscript --vanilla scripts/genelevel.r "+mapping))
        uploadS3("/alignment/data/results/gene_abundance.tsv", str(jj['id'])+"-"+str(jj['uid'])+"_kallisto_gene.tsv", "mssm-seq-generesults")
        uploadS3("/alignment/data/results/runinfo.txt", str(jj['id'])+"-"+str(jj['uid'])+"_kallisto_info.tsv", "mssm-seq-qc")
        
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
        with open('/alignment/data/results/runinfo.txt') as f:
            lines = f.readlines()
            for l in lines:
                if "[quant] processed " in l:
                    sp = l.strip().split(" reads, ")
                    numreads = sp[0].replace("[quant] processed ","").replace(",","")
                    numaligned = sp[1].replace(" reads pseudoaligned","").replace(",","")
                if "[quant] estimated average fragment length: " in l:
                    estimatedlength = l.replace("[quant] estimated average fragment length: ","").replace(",","")
        
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
        r = requests.post('http://amp.pharm.mssm.edu/awsscheduler/uploadgenecount.php?time='+str(str(randint(0,9000000000))), json=sample)
        print("Sent counts to database")
        # clean up after yourself, a bit risky if python fails files fill up container space
        files = glob.glob('/alignment/data/results/*')
        for f in files:
            os.remove(f)
        
        files = glob.glob('/alignment/data/uploads/*')
        for f in files:
            os.remove(f)
