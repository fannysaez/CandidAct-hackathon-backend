FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client libzip-dev libicu-dev zip unzip git \
    && docker-php-ext-install pdo pdo_mysql intl opcache \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

ENV APP_ENV=dev
RUN composer install --optimize-autoloader --no-interaction

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride None\n\
    Require all granted\n\
    FallbackResource /index.php\n\
</Directory>\n' >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data var/

EXPOSE 80