FROM php:7.4-fpm

ENV PANTHER_NO_SANDBOX 1
ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'
ENV PANTHER_CHROME_DRIVER_BINARY /usr/lib/chromium/chromedriver

RUN apt-get update
RUN apt-get install -y bash gnupg2 software-properties-common unzip wget
RUN apt-get install -y libnss3 libx11-6 libzip-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-install zip

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

RUN curl -sS -o - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add
RUN echo "deb [arch=amd64]  http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list
RUN apt-get -y update
RUN apt-get -y install google-chrome-stable

RUN wget https://chromedriver.storage.googleapis.com/2.35/chromedriver_linux64.zip
RUN unzip chromedriver_linux64.zip
RUN mv chromedriver /usr/local/bin/chromedriver
RUN rm chromedriver_linux64.zip

ADD ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
ADD ./error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini

EXPOSE 80

WORKDIR /var/www