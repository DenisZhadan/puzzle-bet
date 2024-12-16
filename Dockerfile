FROM php:8.3.14-fpm-alpine3.21

RUN apk update && apk add --no-cache postgresql-dev \
    && docker-php-ext-configure pdo_pgsql --with-pdo-pgsql=/usr \
    && docker-php-ext-install pdo_pgsql \
    && cp /usr/lib/libpq.so.5 /usr/local/lib/ \
    && apk del postgresql-dev \
    && rm -rf /var/cache/apk/*

COPY --chown=www-data:www-data ["src",  "/var/www/src/"]
COPY --chown=www-data:www-data ["vendor", "/var/www/vendor/"]
COPY --chown=www-data:www-data ["www/index.php", "/var/www/html/"]

COPY --chown=www-data:www-data ["www/index.html", "/var/www/static/"]
COPY --chown=www-data:www-data ["www/admin.html", "/var/www/static/"]
COPY --chown=www-data:www-data ["www/favicon.png", "/var/www/static/"]
COPY --chown=www-data:www-data ["www/css", "/var/www/static/css/"]
COPY --chown=www-data:www-data ["www/js", "/var/www/static/js/"]
