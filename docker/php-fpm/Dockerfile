ARG PHP_VERSION
ARG PHP_XDEBUG
ARG TZ

FROM php:${PHP_VERSION}-fpm

ENV TZ=${TZ}

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN <<EOF
  set -e

  mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
  apt update
  apt-get install -y zlib1g-dev libzip-dev libpng-dev libwebp-dev libjpeg62-turbo-dev zip unzip bash ffmpeg cron procps
  rm -rf /var/lib/apt/lists/* /var/log/*

  ln -sf /bin/bash /bin/sh

  docker-php-ext-install exif
  docker-php-ext-configure gd --with-jpeg --with-webp
  docker-php-ext-install gd
  docker-php-ext-install pdo_mysql
  docker-php-ext-install zip

  echo "date.timezone=${TZ}" > /usr/local/etc/php/conf.d/docker-php-ext-timezone.ini
  echo "memory_limit=512M" > /usr/local/etc/php/conf.d/docker-php-ext-memory.ini
  echo "max_file_uploads=256M" > /usr/local/etc/php/conf.d/docker-php-ext-upload.ini
  echo "post_max_size=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini
  echo "upload_max_filesize=256M" >> /usr/local/etc/php/conf.d/docker-php-ext-upload.ini

  echo "pm = dynamic" >> /usr/local/etc/php-fpm.d/zz-docker.conf
  echo "pm.max_children = 16" >> /usr/local/etc/php-fpm.d/zz-docker.conf
  echo "pm.start_servers = 8" >> /usr/local/etc/php-fpm.d/zz-docker.conf
  echo "pm.min_spare_servers = 4" >> /usr/local/etc/php-fpm.d/zz-docker.conf
  echo "pm.max_spare_servers = 8" >> /usr/local/etc/php-fpm.d/zz-docker.conf

  echo "* * * * * www-data /usr/local/bin/php /vesp/core/cli/cron.php" >> /etc/crontab

  if [ "${PHP_XDEBUG}" = "1" ]; then
      yes | pecl install xdebug-3.3.0
      echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/xdebug.ini
      echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini
      echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini
      echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini
      echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/xdebug.ini;
  fi
EOF
