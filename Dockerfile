#FROM dolibarr/dolibarr:latest

# Creamos el directorio por si no existe y copiamos tu config
#RUN mkdir -p /var/www/html/htdocs/conf
#COPY conf/conf.php /var/www/html/htdocs/conf/conf.php

# Permisos totales para que el instalador de Dolibarr pueda escribir los datos de Supabase
#RUN chown -R www-data:www-data /var/www/html/htdocs/conf/ && \
    #chmod 666 /var/www/html/htdocs/conf/conf.php

#EXPOSE 80
FROM dolibarr/dolibarr:latest

# Copiar config al lugar correcto
COPY conf/conf.php /var/www/html/conf/conf.php

# Permisos
RUN chown -R www-data:www-data /var/www/html/conf/ && \
    chmod 666 /var/www/html/conf/conf.php

EXPOSE 80
