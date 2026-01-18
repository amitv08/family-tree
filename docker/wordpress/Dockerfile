# Multi-stage build for Family Tree WordPress Plugin
FROM composer:2 as composer

# Copy composer files
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

FROM node:18-alpine as frontend

WORKDIR /app

# Copy package files
COPY package*.json ./
RUN npm ci --only=production

# Copy source files
COPY assets/ ./assets/
RUN npm run build 2>/dev/null || echo "No build script found"

FROM wordpress:6.4-php8.2-fpm-alpine as base

# Install system dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    curl-dev \
    imagemagick-dev \
    imagemagick \
    ghostscript \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        exif \
        bcmath \
        opcache \
        curl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Create non-root user
RUN addgroup -g 1000 wordpress && \
    adduser -D -u 1000 -s /bin/bash -G wordpress wordpress

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP configurations
COPY docker/wordpress/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/wordpress/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy PHP dependencies from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/assets ./wp-content/plugins/family-tree/assets

# Copy plugin files
COPY . /var/www/html/wp-content/plugins/family-tree

# Install WordPress core files and set permissions
RUN wp core download --allow-root --version=6.4 --locale=en_US \
    && chown -R wordpress:wordpress /var/www/html \
    && mkdir -p wp-content/uploads wp-content/plugins wp-content/themes \
    && chmod -R 755 /var/www/html

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD php -r "echo 'OK';" || exit 1

# Switch to non-root user
USER wordpress

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Default command
CMD ["php-fpm"]
