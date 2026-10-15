FROM moodlehq/moodle-php-apache:8.3@sha256:7fd5f3356a71889fc6eda2a7cca2b44ed1e3f90556f5c3bcd9f2091789739af2

WORKDIR /var/www/html
RUN curl --fail --location --retry 3 https://getcomposer.org/download/2.8.12/composer.phar -o /usr/local/bin/composer \
    && echo 'f446ea719708bb85fcbf4ef18def5d0515f1f9b4d703f6d820c9c1656e10a2f2  /usr/local/bin/composer' | sha256sum -c - \
    && chmod 755 /usr/local/bin/composer
COPY sources/moodle/composer.json sources/moodle/composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_IPRESOLVE=4 COMPOSER_MAX_PARALLEL_HTTP=2 COMPOSER_PROCESS_TIMEOUT=1800 \
    php -d default_socket_timeout=1800 /usr/local/bin/composer install \
    --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins
COPY sources/moodle/ ./
COPY docker/moodle/config.php ./config.php
COPY docker/health.php ./public/aou-health.php
COPY docker/moodle/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-aou.ini
COPY docker/moodle/cron.sh /opt/aou/cron.sh
RUN printf 'Listen 8080\n' > /etc/apache2/ports.conf \
    && mkdir -p /var/www/moodledata \
    && chown www-data:www-data /var/www/moodledata \
    && a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
LABEL org.opencontainers.image.revision="344232c15336c71b80f9aca8359ce0e0a9f3d116"
