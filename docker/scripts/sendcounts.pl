file = "/alignment/data/results/gene_abundance.tsv"
if os.path.exists():



sample = {
    'id': str(jj['id']),
    'uid': str(jj[uid]),
    'genesymbols': genesymbols,
    'values': values
}
r = requests.post('http://amp.pharm.mssm.edu/scheduler/uploadgenecount.php', json=sample)



