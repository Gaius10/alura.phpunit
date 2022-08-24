FROM php

RUN docker-php-source extract

RUN pecl install ds xdebug

RUN docker-php-ext-enable ds
RUN docker-php-ext-enable xdebug

RUN docker-php-source delete

# COPY . .
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ENV XDEBUG_SESSION 1

RUN mkdir -p /workspace
WORKDIR /workspace

CMD ["vendor/bin/phpunit"]