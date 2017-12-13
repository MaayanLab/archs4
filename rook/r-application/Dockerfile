FROM ubuntu:latest

RUN apt-get update
RUN apt-get -y install r-base r-base-dev

RUN R -e 'install.packages("Rook", repos="http://cran.rstudio.org")'
RUN R -e 'install.packages("jsonlite", repos="http://cran.rstudio.org")'
RUN R -e 'install.packages("httpRequest", repos="http://cran.rstudio.org")'

COPY . /app

EXPOSE 8080

WORKDIR /app

CMD Rscript /app/run.R