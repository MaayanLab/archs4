#!/bin/bash
while [ 1 ]; do
    python scripts/allAlign.py
    #rm /alignment/data/uploads/*
    #rm /alignment/data/results/*
    sleep 10s
done