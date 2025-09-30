#!/bin/bash
# Ajustar permisos para Laravel
sudo chmod -R 775 /var/app/current/storage /var/app/current/bootstrap/cache
sudo chown -R webapp:webapp /var/app/current/storage /var/app/current/bootstrap/cache

# Ejecutar migraciones automáticamente
cd /var/app/current
php artisan migrate --force
