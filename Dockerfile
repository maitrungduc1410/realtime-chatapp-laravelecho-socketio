# Set master image
FROM php:7.2-fpm-alpine

LABEL maintainer="Mai Trung Duc (maitrungduc1410@gmail.com)"

# Set working directory
WORKDIR /var/www/html

# Install Additional dependencies
RUN apk update && apk add --no-cache \
    build-base shadow supervisor \
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
RUN docker-php-ext-install pcntl

# Remove Cache
RUN rm -rf /var/cache/apk/*

COPY .docker/supervisord.conf /etc/supervisord.conf
COPY .docker/supervisor.d /etc/supervisor.d

# COPY .docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
# COPY .docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Use the default production configuration ($PHP_INI_DIR variable already set by the default image)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add UID '1000' to www-data
RUN usermod -u 1000 www-data

# Copy existing application directory permissions
COPY . .

RUN chown -R www-data:www-data .

ENV ENABLE_CRONTAB 1
ENV ENABLE_HORIZON 1
ENV CURRENT_USER www-data

ENTRYPOINT ["sh", "/var/www/html/.docker/docker-entrypoint.sh"]

CMD supervisord -n -c /etc/supervisord.conf