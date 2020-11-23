FROM php:7.3-fpm

RUN apt-get update && apt-get install -y libcurl4-gnutls-dev libc-client-dev libkrb5-dev libzip-dev gettext unzip sudo apt-utils wget

RUN pecl install xdebug && docker-php-ext-enable xdebug \
    && docker-php-ext-install zip

# Composer installation
COPY composer.sh /tmp/composer.sh
RUN chmod +x /tmp/composer.sh && /tmp/./composer.sh

# NodeJs installation
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash - \
    && apt update && apt install -y nodejs

# Yarn installation
RUN apt install -y gnupg2 \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \ 
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list \
    && apt update && apt install -y yarn \
    && yarn --version