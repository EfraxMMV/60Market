FROM php:8.4-apache

# Copiar el instalador oficial de extensiones de PHP
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Instalar utilidades básicas del sistema
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones requeridas de PHP (PostgreSQL, Zip, BCMath y Redis)
RUN install-php-extensions pdo_pgsql zip bcmath redis

# Habilitar mod_rewrite en Apache para el enrutamiento de Laravel
RUN a2enmod rewrite

# Copiar ejecutable de Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar todo el código de la aplicación
COPY . .

# Instalar dependencias PHP de producción sin paquetes de desarrollo
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos requeridos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Apuntar Apache al directorio public/ de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]