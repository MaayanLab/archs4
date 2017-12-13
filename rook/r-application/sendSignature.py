
import subprocess

import MySQLdb
import datetime, time
import numpy as np
import pandas as pd
import subprocess
import shlex
import os
import sys
import hashlib
import requests
import json
import re
import geode

from joblib import Parallel, delayed
import multiprocessing

import smtplib
from email.mime.text import MIMEText

import pickle
from collections import defaultdict
import zipfile
import zlib
import gzip



vector_json = {}
vector_json["signatureName"] = "hai"
#vector_json["filter"] = "N_row_sum"

vector_json["genes"] = ["KRT17","SLC7A5","FBN1","TGFBI","SPTAN1","HSPG2","SLC3A2", "COL12A1", "TKT", "ACTN4"]

upload_url = 'http://amp.pharm.mssm.edu/custom/rooky'

print(json.dumps(vector_json))

r = requests.post(upload_url, json.dumps(vector_json))

print(r.text)




































