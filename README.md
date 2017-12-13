# ARCHS4

ARCHS4 provides access to gene counts from HiSeq 2000 and HiSeq 2500 platforms for human and mouse experiments from GEO and SRA. The website enables downloading of the data in H5 format for programmatic access as well as a 3-dimensional view of the sample and gene spaces. Search features allow browsing of the data by meta data annotation, ability to submit your own up and down gene sets, and explore matching samples enriched for annotated gene sets. Selected sample sets can be downloaded into a tab separated text file through auto-generated R scripts for further analysis. Reads are aligned with Kallisto using a custom cloud computing platform. Human samples are aligned against the GRCh38 human reference genome, and mouse samples against the GRCm38 mouse reference genome.<br>
<br><br>
Website: <a href="https://amp.pharm.mssm.edu/archs4">https://amp.pharm.mssm.edu/archs4</a><br>
BioRxiv: <a href="https://www.biorxiv.org/content/early/2017/09/15/189092">https://www.biorxiv.org/content/early/2017/09/15/189092</a>
<br><br>
The collection of scripts is provided as is and there is currently no streamlined instructions how to use it in other projects. The alignment and processing of RNA-seq samples encompasses a large amount of prerequisites. In the future this code base will be cleaned up and made more user friendly. Running the code will require Docker/Marathon/Mesos, Python and R as well as access to the Amazon Cloud.
