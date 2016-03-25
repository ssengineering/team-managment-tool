FROM php:5.6-apache

RUN apt-get update && apt-get install -y libmcrypt-dev \
	libxml2-dev libssl-dev ssmtp libldap2-dev 
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install mysql pdo pdo_mysql mcrypt xml soap ldap
RUN pecl install mongo

RUN a2enmod ssl
RUN a2enmod rewrite

COPY dev/conf/installphpredis.sh /tmp/installphpredis.sh
RUN chmod +x /tmp/installphpredis.sh
RUN /tmp/installphpredis.sh

RUN sed -i '/^mailhub=.*/ c\mailhub=${EMAIL_GATEWAY_ADDRESS}' /etc/ssmtp/ssmtp.conf
RUN sed -i '/^hostname=.*/ c\hostname=${DEV_URL}' /etc/ssmtp/ssmtp.conf
RUN echo 'FromLineOverride=YES' >> /etc/ssmtp/ssmtp.conf

COPY dev/conf/apache.conf /etc/apache2/sites-enabled/apache.conf
COPY dev/certs /tmp
COPY dev/conf/php.ini /usr/local/etc/php/php.ini

EXPOSE 443
