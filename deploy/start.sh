#!/bin/sh
set -eu

PORT="${PORT:-8080}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads

cp /usr/local/share/adnu-uploads.htaccess /var/www/html/uploads/.htaccess

exec apache2-foreground
