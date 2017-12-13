# get rid of old stuff
docker rmi -f $(docker images | grep "^<none>" | awk "{print $3}")
docker rm $(docker ps -q -f status=exited)

#docker build -f DockerStar -t maayanlab/aligner-amazon .
docker build -f Dockerfile -t maayanlab/rook .

#docker push maayanlab/aligner-amazon
docker push maayanlab/rook

# docker run -d -v /Users/maayanlab/data:/alignment/data/ --name="aligner" maayanlab/alignerutils-amazon

#sudo docker run -d --name="kallisto" maayanlab/cloudkallisto

