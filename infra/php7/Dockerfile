FROM php:7.2-cli

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
COPY ./infra/php7/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# entrypoint
COPY --chmod=700 ./infra/php7/entrypoint.sh /usr/local/bin/
ENTRYPOINT ["entrypoint.sh"]

VOLUME /usr/src/app
WORKDIR /usr/src/app
