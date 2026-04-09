FROM dolibarr/dolibarr:latest

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
