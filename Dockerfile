FROM php:8.3.9-fpm-alpine3.20 AS base
WORKDIR /app
COPY . .
# Add and Enable PHP-PDO Extenstions
RUN docker-php-ext-install pdo pdo_mysql pcntl
RUN docker-php-ext-enable pdo_mysql

FROM base AS reverb
CMD ["php", "artisan", "reverb:start"]

FROM base AS workers
CMD ["php", "artisan", "queue:work"]

FROM base AS nonroot
RUN addgroup -g 1000 myusergroup
RUN adduser -D -u 1000 myuser -G myusergroup
RUN chown -R myuser:myusergroup .
USER myuser

FROM nonroot AS reverb-nonroot
CMD ["php", "artisan", "reverb:start"]

FROM nonroot AS workers-nonroot
CMD ["php", "artisan", "queue:work"]