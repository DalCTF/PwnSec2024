FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html/
RUN echo '\n<FilesMatch "\.sql$">\n        Require all denied\n</FilesMatch>' >> /etc/apache2/apache2.conf

EXPOSE 80
