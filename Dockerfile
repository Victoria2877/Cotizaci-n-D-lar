# PHP + Apache
FROM php:8.2-apache

# Paquetes del sistema y extensiones PHP necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev nodejs npm \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite

# Ajustar DocumentRoot a /public
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's#<Directory /var/www/html/>#<Directory /var/www/html/public/>#g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Composer (desde imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos código e instalamos dependencias
COPY . /var/www/html

# Instalación prod de PHP y build de assets
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
 && npm ci && npm run build || true

EXPOSE 80
