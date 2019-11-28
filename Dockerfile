# Set master image
FROM php:7.2-fpm-alpine

LABEL maintainer="Mai Trung Duc (maitrungduc1410@gmail.com)"

# Set working directory
WORKDIR /var/www/html

# Install Additional dependencies
RUN apk update && apk add --no-cache \
    build-base shadow vim curl supervisor \
    php7 \
    php7-fpm \
    php7-common \
    php7-pdo \
    php7-pdo_mysql \
    php7-mysqli \
    php7-mcrypt \
    php7-mbstring \
    php7-xml \
    php7-openssl \
    php7-json \
    php7-phar \
    php7-zip \
    php7-gd \
    php7-dom \
    php7-session \
    php7-zlib

# Add and Enable PHP-PDO Extenstions
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql

# Remove Cache
RUN rm -rf /var/cache/apk/*

### Make php bin alias
RUN ln -nfs /usr/bin/php7 /usr/bin/php \
  && ln -nfs /usr/sbin/php-fpm7 /usr/sbin/php-fpm \
  && ln -nfs /etc/php7 /etc/php

COPY .docker/supervisord.conf /etc/supervisord.conf
COPY .docker/supervisor.d /etc/supervisor.d
COPY .docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Add UID '1000' to www-data
RUN usermod -u 1000 www-data

# Copy existing application directory permissions
COPY --chown=www-data:www-data . .

# Change current user to www
USER www-data

# Expose port 9000 for nginx container redirect request to
EXPOSE 9000

CMD supervisord -n -c /etc/supervisord.conf