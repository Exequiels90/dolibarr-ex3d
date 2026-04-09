FROM dolibarr/dolibarr:latest

# Fix apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Script que corre al iniciar
RUN echo '#!/bin/bash\n\
rm -f /var/www/html/conf/conf.php\n\
chmod -R 777 /var/www/html/conf\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]

EXPOSE 80
