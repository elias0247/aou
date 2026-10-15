FROM moodlehq/moodle-php-apache:8.3@sha256:7fd5f3356a71889fc6eda2a7cca2b44ed1e3f90556f5c3bcd9f2091789739af2

# RosarioSIS needs gettext in addition to the extensions in the Moodle image.
RUN docker-php-ext-install -j2 gettext \
    && a2enmod rewrite \
    && rm -f /etc/apache2/conf-enabled/fallback.conf \
    && printf 'Listen 8081\n' > /etc/apache2/ports.conf
WORKDIR /var/www/html
COPY sources/rosariosis/ ./
COPY docker/rosariosis/config.inc.php ./config.inc.php
COPY docker/health.php ./aou-health.php
COPY docker/rosariosis/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-aou.ini
RUN chown -R www-data:www-data assets/FileUploads assets/StudentPhotos assets/UserPhotos \
    && printf 'sendmail_path = /bin/true\n' > /usr/local/etc/php/conf.d/99-aou-mail.ini
ENV APACHE_DOCUMENT_ROOT=/var/www/html
LABEL org.opencontainers.image.revision="5ece843bb8b4589b8568cb0da1929ff03da7e433"
