FROM php:7.1-rc-cli

RUN apt-get update \
    && docker-php-ext-install -j$(nproc) calendar \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/development.ini
