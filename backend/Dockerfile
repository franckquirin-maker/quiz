FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql \
    && apk add --no-cache nginx supervisor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize --no-dev

RUN mkdir -p /var/www/html/public/media \
    && chown -R www-data:www-data /var/www/html \
    && chown -R nginx:nginx /var/lib/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
