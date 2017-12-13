# get rid of old stuff
docker rmi -f $(docker images | grep "^<none>" | awk "{print $3}")
docker rm $(docker ps -q -f status=exited)

docker kill awsjobscheduler
docker rm awsjobscheduler

#docker build -f DockerStar -t maayanlab/aligner-amazon .
docker build -f DockerfileScheduler -t maayanlab/awsjobscheduler .

#docker push maayanlab/aligner-amazon
docker push maayanlab/awsjobscheduler

#docker run -d --name="jobscheduler" -p 8989:80 maayanlab/awsjobscheduler

