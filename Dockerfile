# 1. Fase de Construcción (Build Stage)
# Usamos una imagen base que incluye PHP 8.x y las extensiones comunes
FROM php:8.3-fpm-alpine AS base

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apk update && apk add --no-cache \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    # Instalar extensiones de PHP
    && docker-php-ext-install pdo_mysql opcache bcmath exif \
    # Instalar Composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 2. Configuración de Producción
FROM base AS final

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación (excluyendo vendor/, que instalaremos)
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Configuración de permisos de almacenamiento
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar la configuración de Nginx y el script de inicio
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Exponer el puerto
EXPOSE 8080

# Comando de inicio del contenedor (Lo que Render ejecutará)
CMD ["/usr/local/bin/start.sh"]