FROM php:8.2-cli

# install PHP extensions manager
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# PHP extensions
RUN install-php-extensions \
    @composer \
    curl \
    json \
    xml \
    xdebug

# XDebug config
COPY ./infra/php8/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

VOLUME /usr/src/app
WORKDIR /usr/src/app
CMD echo "php8 has started..." && tail -f /dev/null
