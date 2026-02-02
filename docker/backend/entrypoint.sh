#!/bin/sh
php artisan key:generate --force
php artisan migrate --force --seed

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
