#!/usr/bin/python
import datetime, time
import subprocess
import shlex
import os
import sys
import tinys3
import glob
import boto

def uploadS3(file, key, bucket):
    conn = tinys3.Connection("", "", tls=True)
    f = open(file,'rb')
    conn.upload(key, f, bucket)

f = open('logs/upload.log', 'r')
x = list(set(f.readlines()))
os.remove("logs/upload.log")

for f in x:
    f = f.rstrip()
    uploadS3("uploads/"+f, f, "seq-raw");
    target = open("logs/waiting.log", 'a')
    target.write(f+"\n")
    target.close()


