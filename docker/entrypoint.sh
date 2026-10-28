#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Esperando a MySQL en $DB_HOST:$DB_PORT..."
    until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' 2>/dev/null; do
        sleep 2
    done
fi

php artisan migrate --force

if [ "$SEED_DATABASE" = "true" ] && [ "$(php artisan tinker --execute='echo App\Models\Producto::count();')" = "0" ]; then
    php artisan db:seed --force
fi

exec "$@"
