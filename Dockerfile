FROM composer AS composer

# copying the source directory and install the dependencies with composer
COPY ./ /app

# run composer install to install the dependencies
RUN composer install \
  --optimize-autoloader \
  --no-interaction \
  --no-progress

# continue stage build with the desired image and copy the source including the
# dependencies downloaded by composer
FROM trafex/php-nginx

# Switch to root to install additional php extensions
USER root
RUN apk add --no-cache \
php81-posix \
php81-pcntl

# Copy composer depedencies to app directory
COPY --chown=nginx --from=composer /app /var/www/html

# Give app files permission to write files
RUN chown -R nobody:nobody /var/www/html

# Switch back to nobody
USER nobody

# Configure nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY fpm-pool.conf /etc/php81/php-fpm.d/www.conf

# Configure supervisord
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Let supervisord start nginx & php-fpm
# (this CMD overwrites CMD in php-nginx's Dockefile)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
