FROM php:8.3-apache

# Instala e habilita OPcache para máxima performance em produção (Dokploy)
RUN docker-php-ext-install opcache

# Habilita módulos do Apache necessários para rotas amigáveis, proxy reverso (Traefik/Dokploy) e compressão
RUN a2enmod rewrite headers expires deflate setenvif remoteip

# Configura ServerName, suporte a proxy HTTPS (Traefik/Dokploy) e permite .htaccess em /var/www/html
RUN { \
      echo "ServerName localhost"; \
      echo "RemoteIPHeader X-Forwarded-For"; \
      echo 'SetEnvIf X-Forwarded-Proto "^https$" HTTPS=on'; \
    } >> /etc/apache2/apache2.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Configurações de produção de PHP e OPcache (UTF-8, timezone e cache de bytecode)
RUN { \
      echo 'default_charset = "UTF-8"'; \
      echo 'date.timezone = "America/Sao_Paulo"'; \
      echo 'memory_limit = 256M'; \
      echo 'expose_php = Off'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
      echo 'opcache.enable = 1'; \
      echo 'opcache.memory_consumption = 128'; \
      echo 'opcache.interned_strings_buffer = 16'; \
      echo 'opcache.max_accelerated_files = 10000'; \
      echo 'opcache.validate_timestamps = 1'; \
      echo 'opcache.revalidate_freq = 2'; \
    } > /usr/local/etc/php/conf.d/viascriptura.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS http://localhost/healthz || exit 1
