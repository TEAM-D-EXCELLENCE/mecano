#!/bin/sh
set -e

echo "=============================="
echo "  Mecano API – Boot sequence  "
echo "=============================="

# ------------------------------------------------------------------
# 0. Refuser de démarrer sans clé d'application
# ------------------------------------------------------------------
# Sans APP_KEY, Laravel démarre puis échoue au premier chiffrement, avec une
# erreur qui ne dit pas d'où vient le problème. Mieux vaut s'arrêter ici.
if [ -z "$APP_KEY" ]; then
    echo "[boot] ERREUR : APP_KEY est vide."
    echo "[boot] Générez-la avec : php artisan key:generate --show"
    exit 1
fi

# ------------------------------------------------------------------
# 1. Wait for the database to be reachable (optional, graceful)
# ------------------------------------------------------------------
if [ -n "$DB_HOST" ]; then
    echo "[boot] Waiting for DB at ${DB_HOST}:${DB_PORT:-5432}..."
    timeout=60
    elapsed=0
    until php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-postgres}', '${DB_USERNAME:-postgres}', '${DB_PASSWORD}');" 2>/dev/null; do
        if [ "$elapsed" -ge "$timeout" ]; then
            echo "[boot] WARNING: DB not ready after ${timeout}s – continuing anyway."
            break
        fi
        sleep 2
        elapsed=$((elapsed + 2))
    done
    echo "[boot] DB is reachable."
fi

# ------------------------------------------------------------------
# 2. Cache configuration & routes for production performance
# ------------------------------------------------------------------
echo "[boot] Caching config..."
php artisan config:cache
echo "[boot] Caching routes..."
php artisan route:cache
echo "[boot] Caching views..."
php artisan view:cache

# ------------------------------------------------------------------
# 3. Run migrations (safe – uses --force in production)
# ------------------------------------------------------------------
# `--isolated` prend un verrou applicatif : si plusieurs conteneurs démarrent
# ensemble, un seul migre et les autres passent leur tour au lieu d'appliquer
# les mêmes migrations en concurrence.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[boot] Running migrations..."
    php artisan migrate --force --isolated --no-interaction
else
    echo "[boot] Migrations désactivées (RUN_MIGRATIONS=false)."
fi

# ------------------------------------------------------------------
# 4. Create symlink for storage (if not already done)
# ------------------------------------------------------------------
php artisan storage:link --force 2>/dev/null || true

echo "[boot] Boot complete. Starting supervisord..."

# ------------------------------------------------------------------
# 5. Hand off to supervisord (runs nginx + php-fpm)
# ------------------------------------------------------------------
exec "$@"
