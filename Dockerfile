# Imagen base de PHP 8.4 con
FROM php:8.4-apache
# Instalar dependencias del sistema y extensiones necesarias (PostgreSQL, Redis, Zip)
RUN apt-get update &amp;&amp; apt-get install -y \
	libpq-dev \
	libzip-dev \
	zip \
	unzip \
	git \	
	curl \
	&& docker-php-ext-install pdo pdo_pgsql zip bcmath \
	&& pecl install redis \
	&& docker-php-ext-enable redis
# Habilitar mod\_rewrite en Apache para el enrutamiento de Laravel
RUN a2enmod rewrite 
# Copiar ejecutable de Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer 
# Configurar directorio de trabajo 
WORKDIR /var/www/html
# Copiar todo el código de UMPV al contenedor
COPY . .
# Instalar dependencias PHP de producción
RUN composer install --no-dev --optimize-autoloader
# Configurar permisos requeridos para Laravel 
RUN chown -R www-data:www-data 
/var/www/html/storage 
/var/www/html/bootstrap/cache \
	&& chmod -R 775 /var/www/html/storage
/var/www/html/bootstrap/cache 
# Apuntar Apache al directorio public/ de Laravel 
ENV APACHE\_DOCUMENT\_ROOT /var/www/html/public 
RUN sed -ri -e 's!/var/www/html!${APACHE\_DOCUMENT\_ROOT}!g'
/etc/apache2/sites-available/0000-default.conf 
RUN sed -ri -e 's!/var/www/html!${APACHE\_DOCUMENT\_ROOT}!g'
/etc/apache2/apache2.conf
# Exponer el puerto predeterminado de Apache
EXPOSE 80 
CMD ["apache2-foreground"]
