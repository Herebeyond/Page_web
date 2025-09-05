FROM php:8.2-apache

# Install PostgreSQL dependencies and other required extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

# Copy application files
COPY ./Web /var/www/html

# Enable Apache modules
RUN a2enmod rewrite

# Copy SSL certificate for PHP configuration (if exists)
COPY ./cacert.pem /etc/ssl/certs/cacert.pem 2>/dev/null || echo "No cacert.pem found, skipping..."

# Configure PHP to use SSL certificate (if available)
RUN if [ -f /etc/ssl/certs/cacert.pem ]; then \
        echo "curl.cainfo = /etc/ssl/certs/cacert.pem" >> /usr/local/etc/php/conf.d/curl-ca.ini && \
        echo "openssl.cafile = /etc/ssl/certs/cacert.pem" >> /usr/local/etc/php/conf.d/openssl-ca.ini; \
    fi

# Copy composer.json for dependency management
COPY composer.json /var/www/html/composer.json

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Default command
CMD ["apache2-foreground"]