FROM php:latest

RUN apt-get update && apt-get install -y libssl-dev netcat mysql-client
RUN apt-get clean
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN pecl install mongo redis

RUN curl https://phar.phpunit.de/phpunit.phar -O
RUN chmod +x phpunit.phar
RUN mv phpunit.phar /usr/local/bin/phpunit

COPY unit-tests.sh unit-tests.sh
RUN chmod +x unit-tests.sh
COPY integration-tests.sh integration-tests.sh
RUN chmod +x integration-tests.sh
COPY php.ini /usr/local/etc/php/php.ini

