#!/bin/sh
set -e

echo "=============================="
echo "  Mecano API – Boot sequence  "
echo "=============================="

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
echo "[boot] Running migrations..."
attempt=1
until php artisan migrate --force --no-interaction; do
    if [ "$attempt" -ge 3 ]; then
        echo "[boot] WARNING: migrations failed after ${attempt} attempts - starting anyway."
        echo "[boot] Relancez manuellement : docker compose exec api php artisan migrate --force"
        break
    fi
    attempt=$((attempt + 1))
    echo "[boot] Migration failed - retry ${attempt}/3 in 5s..."
    sleep 5
done

# ------------------------------------------------------------------
# 4. Create symlink for storage (if not already done)
# ------------------------------------------------------------------
php artisan storage:link --force 2>/dev/null || true

echo "[boot] Boot complete. Starting supervisord..."

# ------------------------------------------------------------------
# 5. Hand off to supervisord (runs nginx + php-fpm)
# ------------------------------------------------------------------
exec "$@"
