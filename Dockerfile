FROM ubuntu:16.04

#Basics
RUN apt-get update && apt-get upgrade -y
RUN apt-get install nano -y

#Apache and PHP
RUN apt-get install libapache2-mod-php7.0 php7.0-curl memcached curl -y
RUN a2enmod rewrite
RUN rm -Rf /var/www/html/
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/public/' /etc/apache2/sites-enabled/000-default.conf
EXPOSE 80

CMD apache2ctl start && /bin/bash
