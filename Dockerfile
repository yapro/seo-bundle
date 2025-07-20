FROM php:7-fpm-buster

# Репозитории Debian Buster (10) были удалены с основных зеркал, так как достигли конца срока поддержки (EOL — End of Life).
# В результате они перемещены в архив archive.debian.org.
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list && \
    sed -i 's|http://security.debian.org|http://archive.debian.org/debian-security|g' /etc/apt/sources.list && \
    sed -i '/^deb.*security.*buster.*updates/d' /etc/apt/sources.list && \
    echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99no-check-valid-until && \
    apt-get update

# используем apt-get вместо apt, чтобы не получать: WARNING: apt does not have a stable CLI interface. Use with caution in scripts.
RUN apt-get update

# чтобы при установке apt-пакетов не возникало предупреждения: debconf: delaying package configuration, since apt-utils is not installed
RUN apt install -y apt-utils

# Чтобы composer install не выдавал ошибку: Failed to download XXX from dist: The zip extension and unzip command are both missing, skipping.
RUN apt-get install -y \
    zip \
    libzip-dev
RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Install composer
RUN curl https://getcomposer.org/download/2.0.12/composer.phar --output /usr/bin/composer && \
    chmod +x /usr/bin/composer

# Install xdebug extension
RUN case "$PHP_VERSION" in ( "8"* ) pecl install xdebug;; ( * ) pecl install  xdebug-3.1.5;; esac && \
    docker-php-ext-enable xdebug

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV COMPOSER_HOME=/tmp/composer-home
RUN mkdir $COMPOSER_HOME
# Сохраняем конфигурацию глобально в файле: $COMPOSER_HOME/config.json
RUN composer config --global "preferred-install.yapro/*" source
# Check alternative: composer update yapro/* --prefer-source
RUN chmod -R 777 $COMPOSER_HOME
