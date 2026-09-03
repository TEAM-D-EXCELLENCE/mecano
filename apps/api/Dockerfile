# ============================================================
# Stage 1 – PHP dependencies (Composer)
# ============================================================
FROM composer:2.8 AS vendor

WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ============================================================
# Stage 2 – Production image
# ============================================================
FROM php:8.4-fpm-alpine AS production

LABEL maintainer="Excellence Team" \
      description="Mecano API – Laravel 13 backend" \
      org.opencontainers.image.source="https://github.com/TEAM-D-EXCELLENCE/mecano"

# ------------------------------------------------------------------
# System dependencies + PHP extensions
# ------------------------------------------------------------------
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        bash \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        icu-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

# ------------------------------------------------------------------
# PHP-FPM & OPcache configuration
# ------------------------------------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# ------------------------------------------------------------------
# Nginx configuration
# ------------------------------------------------------------------
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# ------------------------------------------------------------------
# Supervisord – orchestrates nginx + php-fpm in one container
# ------------------------------------------------------------------
COPY docker/supervisord.conf /etc/supervisord.conf

# ------------------------------------------------------------------
# Application code
# ------------------------------------------------------------------
WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY . .

# Remove dev-only files not needed at runtime
RUN rm -rf \
        tests \
        .editorconfig \
        .styleci.yml \
        phpunit.xml \
        composer.phar \
        node_modules \
        .github

# Permissions & storage bootstrap
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Run post-autoload-dump manually (scripts were skipped above)
RUN php artisan package:discover --ansi 2>/dev/null || true

# ------------------------------------------------------------------
# Entrypoint
# ------------------------------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
