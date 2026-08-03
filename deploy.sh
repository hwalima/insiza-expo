#!/bin/bash
# deploy.sh — Pull latest code and restart the Laravel app
# Triggered by the /deploy webhook or run manually on the server

set -e

APP_DIR="$(dirname "$0")"
PUBLIC_HTML="/home/insizaex/public_html"

cd "$APP_DIR"

echo "==> Pulling latest from GitHub..."
git pull origin master

echo "==> Copying compiled assets to public_html..."
cp -r public/build "$PUBLIC_HTML/build"

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Rebuilding caches..."
php artisan optimize:clear
php artisan optimize

echo "==> Done!"

echo ""
echo "✓ Deploy complete!"
