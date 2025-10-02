# ----------------------------------------------------
# FASE 1: CONSTRUCCIÓN Y DEPENDENCIAS (Base Stage)
# ----------------------------------------------------
FROM php:8.3-fpm-alpine AS base

# 1. Instalar dependencias del sistema
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    # MySQL client es útil para la depuración
    mysql-client \
    # Extensiones de PHP
    && docker-php-ext-install pdo_mysql opcache bcmath exif \
    # Instalar Composer globalmente
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo (donde estará la app)
WORKDIR /var/www/html

# 2. Copiar el código fuente
COPY . .

# 3. Instalar dependencias de Laravel
# --no-scripts es CRUCIAL para evitar el error de Pusher/APP_KEY durante la compilación
RUN composer install --no-dev --optimize-autoloader --no-scripts

# ----------------------------------------------------
# FASE 2: FINAL DE EJECUCIÓN (Final Stage)
# ----------------------------------------------------
FROM php:8.3-fpm-alpine

# Instalar Nginx y utilidades de nuevo en el Stage final, ya que no se copian automáticamente
RUN apk update && apk add --no-cache nginx

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del Stage base
COPY --from=base /var/www/html /var/www/html

# 1. Configuración de permisos de Laravel (esencial)
# El usuario 'www-data' debe poder escribir en estas carpetas
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 2. Copiar la configuración de Nginx (debe existir en docker/nginx/default.conf)
# NOTA: Usamos el puerto 8080 en esta configuración.
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# 3. Cloud Run inyecta la variable PORT, pero la exponemos
ENV PORT 8080
EXPOSE 8080

# 4. Comando de inicio (CMD)
# El comando final inicia PHP-FPM y Nginx a la vez. 
# Esto es la solución al error "failed to start and listen on the port 8080".
CMD sh -c "php-fpm && nginx -g 'daemon off;'"