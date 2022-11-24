#!/usr/bin/env sh

docker run -p 80:8080 \
-v "`pwd`/nginx.conf:/etc/nginx/nginx.conf" \
-v "`pwd`/supervisord.conf:/etc/supervisor/conf.d/supervisord.conf" \
-v "`pwd`/fpm-pool.conf:/etc/php81/php-fpm.d/www.conf" \
-v "`pwd`/data:/var/www/html/data" \
eugrade
