FROM dolibarr/dolibarr:latest

# eliminar config vieja que rompe todo
RUN rm -f /var/www/html/conf/conf.php

# evitar warning apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
