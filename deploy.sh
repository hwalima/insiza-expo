#!/bin/bash
# deploy.sh — Pull latest code and restart the Laravel app
# Run this on the cPanel server after each GitHub push:  bash deploy.sh

set -e  # stop on any error

cd "$(dirname "$0")"

echo "==> Pulling latest from GitHub..."
git pull origin master

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Clearing old caches..."
php artisan cache:clear

echo "==> Building frontend assets..."
npm ci --production=false
npm run build

echo ""
echo "✓ Deploy complete!"
