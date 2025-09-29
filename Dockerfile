FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite

# DocumentRoot -> /public
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Habilitar .htaccess para Laravel
RUN bash -lc 'cat > /etc/apache2/conf-available/laravel.conf <<EOF
<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>
EOF' && a2enconf laravel

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
 && npm ci && npm run build || true

EXPOSE 80

