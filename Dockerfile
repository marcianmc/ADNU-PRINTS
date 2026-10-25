FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY . /var/www/html/
COPY php.ini /usr/local/etc/php/conf.d/adnu.ini
COPY deploy/database.php /var/www/html/config/database.php
COPY deploy/start.sh /usr/local/bin/start-adnu.sh
COPY uploads/.htaccess /usr/local/share/adnu-uploads.htaccess

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "/usr/local/bin/start-adnu.sh"]