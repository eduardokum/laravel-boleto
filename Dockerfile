FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
  openssl \
  bash \
  unzip \
  vim \
  $PHPIZE_DEPS \
  libzip-dev \
  zlib-dev \
  libsodium-dev \
  icu-dev \
  icu-data-full \
  libpng-dev \
  linux-headers

RUN docker-php-ext-configure intl
RUN docker-php-ext-install zip sodium intl gd
RUN docker-php-ext-enable zip sodium

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

EXPOSE 9000

ENTRYPOINT ["php-fpm"]
