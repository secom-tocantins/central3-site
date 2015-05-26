FROM ubuntu:14.04.2

RUN apt-get update 
RUN apt-get upgrade -y
RUN apt-get install build-essential -y
RUN apt-get install php5 memcached php5-memcached php5-curl -y 

CMD ["/bin/bash"]
