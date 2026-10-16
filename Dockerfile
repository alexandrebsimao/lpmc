FROM php:8.3-apache

# Habilita módulos do Apache necessários para rotas amigáveis e compressão
RUN a2enmod rewrite headers expires deflate

# Configura ServerName e permite .htaccess em /var/www/html
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurações recomendadas de PHP (UTF-8 e timezone)
RUN { \
      echo 'default_charset = "UTF-8"'; \
      echo 'date.timezone = "America/Sao_Paulo"'; \
      echo 'memory_limit = 256M'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
    } > /usr/local/etc/php/conf.d/viascriptura.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
