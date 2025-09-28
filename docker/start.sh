#!/bin/sh

# Inicia el motor de PHP
/usr/sbin/php-fpm83 -D

# Inicia el servidor Nginx
nginx -g "daemon off;"