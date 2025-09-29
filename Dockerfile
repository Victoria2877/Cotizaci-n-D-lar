FROM php:8.2-apache

# Paquetes + extensiones PHP
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite

# DocumentRoot -> /public
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Habilitar .htaccess para Laravel (AllowOverride All)
RUN printf '%s\n' \
  '<Directory /var/www/html/public>' \
  '    AllowOverride All' \
  '    Require all granted' \
  '</Directory>' \
  > /etc/apache2/conf-available/laravel.conf \
  && a2enconf laravel

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código
COPY . /var/www/html

# Permisos para cache y logs
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Dependencias y build de assets
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
 && npm ci && npm run build || true

EXPOSE 80
