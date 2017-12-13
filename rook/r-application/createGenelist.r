library("rhdf5")
library("Rtsne")
library("mygene")
library("preprocessCore")
setwd("~/OneDrive/rook/r-application")


file = "~/OneDrive/idg/human_matrix.h5"

res = h5ls(file)
nsamples = as.numeric(res[4,5])
ngenes = as.numeric(res[5,5])

samples = h5read(file, "meta/samples")
tissue = h5read(file, "meta/tissue")
genes = h5read(file, "meta/genes")

H5close()

rsample = sample(samples)

fl = ceiling(length(samples)/20)


diffup = list()
diffdown = list()

for(i in 1:20){
    print(i)
    ra = ((i-1)*fl+1):min(length(samples),(i*fl))
    sa = match(rsample[ra], samples)
    
    i = 1
    expt = h5read(file, "data/expression", index=list(1:length(genes), sa))
    expt = log2(expt+1)
    expt = normalize.quantiles(expt)
    rownames(expt) = genes
    colnames(expt) = rsample[ra]
    H5close()

    sexp = t(scale(t(expt)))
    
    for(j in 1:ncol(sexp)){
        oo = order(sexp[,j])
        down = genes[oo[1:500]]
        up = genes[rev(oo)[1:500]]
        diffup[[length(diffup)+1]] = up
        diffdown[[length(diffdown)+1]] = down
    }
}

humangenes = genes

names(diffup) = rsample
names(diffdown) = rsample
humandiff = list(diffup, diffdown)
save(humandiff, humangenes, file="human_diff.rda")


si = sample(1:1000, 10)

for(k in si){

    ild = rep(0, length(rsample))
    ilu = rep(0, length(rsample))

    t = proc.time()
    dc = (length(diffdown[[k]])*length(diffdown[[k]]))/2000

    for(i in 1:length(diffdown)){
        ild[i] = length(intersect(diffdown[[i]], diffdown[[k]][1:100]))
        ilu[i] = length(intersect(diffup[[i]], diffup[[k]][1:100]))
    }

    print(proc.time() - t)


    susu = scale(scale(ild)+scale(ilu))
    ww = which(susu > 4)

    colo = rep(1, length(susu))
    colo[ww] = 2

    plot(scale(ild), scale(ilu), pch=".", col=colo)

    print(length(ww))

}

plot(density(susu))




