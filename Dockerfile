FROM php:8.1.15-cli-alpine

COPY --from=composer:2.5.2 /usr/bin/composer /usr/local/bin/composer
COPY . /srv
WORKDIR /srv

ARG COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --optimize-autoloader --no-interaction --no-ansi -o

VOLUME /srv/data/storage/

CMD ["/bin/sh", "-c", "php /srv/data/storage.init.php && php -S 0.0.0.0:8080 -t /srv/public"]
