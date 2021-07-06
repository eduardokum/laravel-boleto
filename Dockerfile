FROM php:7.3.25-fpm-alpine3.11

RUN apk add --no-cache \
  openssl \
  bash \
  unzip \
  vim \
  $PHPIZE_DEPS \
  libzip-dev \
  zlib-dev \
  libsodium-dev \
  icu-dev

RUN docker-php-ext-configure intl
RUN docker-php-ext-install zip sodium intl
RUN docker-php-ext-enable zip sodium

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



WORKDIR /var/www

EXPOSE 9000

ENTRYPOINT ["php-fpm"]
