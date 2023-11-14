ARG PHP_VERSION

FROM php:${PHP_VERSION}-fpm
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG TIME_ZONE
ENV TZ=${TIME_ZONE}
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt update
RUN apt-get install -y zlib1g-dev libzip-dev libpng-dev libwebp-dev libjpeg62-turbo-dev zip unzip bash ffmpeg cron procps

RUN ln -sf /bin/bash /bin/sh

RUN docker-php-ext-configure gd --with-jpeg --with-webp && docker-php-ext-install gd
RUN docker-php-ext-install exif
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo_mysql

RUN echo "date.timezone=${TZ}" > /usr/local/etc/php/conf.d/docker-php-ext-timezone.ini
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/docker-php-ext-memory.ini
RUN echo "max_file_uploads=256M" > /usr/local/etc/php/conf.d/docker-php-ext-upload.ini \
  && echo "post_max_size=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini \
  && echo "upload_max_filesize=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini

RUN echo "* * * * * www-data /usr/local/bin/php /vesp/core/cli/cron.php" >> /etc/crontab

ARG PHP_XDEBUG
RUN if [ "$PHP_XDEBUG" = "1" ]; then \
      yes | pecl install xdebug-3.2.0 \
          && echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
          && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    fi
