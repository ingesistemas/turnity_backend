# ----------------------------------------------------
# FASE 1: CONSTRUCCIÓN Y DEPENDENCIAS (Base Stage)
# ----------------------------------------------------
FROM php:8.3-fpm-alpine AS base

# 1. Instalar dependencias del sistema y extensiones de PHP
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    mysql-client \
    && docker-php-ext-install pdo_mysql opcache bcmath exif \
    # Instalar Composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# 2. Copiar el código fuente
COPY . .

# 3. Instalar dependencias de Laravel
# --no-scripts es crucial para evitar el error de Pusher/APP_KEY en la compilación
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ----------------------------------------------------
# FASE 2: FINAL DE EJECUCIÓN (Final Stage)
# ----------------------------------------------------
FROM php:8.3-fpm-alpine

# Instalar Nginx de nuevo, ya que no se copia automáticamente
RUN apk update && apk add --no-cache nginx

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del Stage base
COPY --from=base /var/www/html /var/www/html

# 1. Configuración de permisos de Laravel (esencial)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 2. Copiar la configuración de Nginx (debe existir en docker/nginx/default.conf)
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# 3. Cloud Run necesita el puerto 8080
ENV PORT 8080
EXPOSE 8080

# 4. Comando de inicio (CMD) - SOLUCIÓN AL FALLO DE ARRANQUE (TIMEOUT)
# Inicia PHP-FPM en segundo plano (&) y luego Nginx en primer plano (exec).
# Esto es la sintaxis correcta para asegurar que Nginx escucha en 8080 y que el contenedor se mantiene vivo.
CMD sh -c "php-fpm & exec nginx -g 'daemon off;'"