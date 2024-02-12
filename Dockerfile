# Set master image
FROM php:8.3-fpm-alpine

LABEL maintainer="Mai Trung Duc (maitrungduc1410@gmail.com)"

# Set working directory
WORKDIR /app

# Install Additional dependencies
RUN apk update && apk add --no-cache supervisor

# Add and Enable PHP-PDO Extenstions
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql
RUN docker-php-ext-install pcntl

# Remove Cache
RUN rm -rf /var/cache/apk/*

# Use the default production configuration ($PHP_INI_DIR is variable already set by the default image)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

#-----------SUPERVISOR------------
COPY .docker/root/supervisord.conf /etc/supervisord.conf

# Task scheduling
RUN echo "* * * * * php /app/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontabs/root

# Laravel horizon
# Note that Horizon already includes worker
# Otherwise we need to run queue:work manually to spawn workers
COPY .docker/root/supervisor.d /etc/supervisor.d

# Copy existing application directory permissions
COPY . .

CMD supervisord -n -c /etc/supervisord.conf
