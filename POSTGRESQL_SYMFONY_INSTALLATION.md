# Installing PostgreSQL Service with Symfony ORM Pack

This document provides all the necessary commands to install and configure PostgreSQL service with the symfony/orm-pack in a Symfony Docker project.

## Overview

This guide will help you:
1. Install symfony/orm-pack using Composer
2. Configure PostgreSQL service in Docker Compose
3. Set up the database connection
4. Initialize and verify the setup

## Prerequisites

- Docker and Docker Compose installed
- PHP project with Composer support
- Basic understanding of Symfony and Docker

## Installation Commands

### 1. Install Symfony ORM Pack

```bash
# Install symfony/orm-pack via Composer
composer require symfony/orm-pack

# Alternative: Install with specific version if needed
composer require symfony/orm-pack:^2.0
```

### 2. Install Additional PostgreSQL Dependencies

```bash
# Install PostgreSQL PDO extension for PHP (if not already installed)
composer require doctrine/dbal

# Install PostgreSQL specific dependencies
composer require doctrine/doctrine-bundle doctrine/orm
```

### 3. Docker Compose Configuration

#### Base Configuration (compose.yaml)

Create or update your `compose.yaml` file:

```yaml
services:
  web:
    build: .
    ports:
      - "80:80"
    depends_on:
      - postgres
    volumes:
      - ./Web:/var/www/html
      - ./php.ini:/usr/local/etc/php/php.ini
    environment:
      # PostgreSQL connection URL for Symfony
      - DATABASE_URL=postgresql://symfony_user:symfony_password@postgres:5432/symfony_db?serverVersion=15&charset=utf8

  postgres:
    image: postgres:15
    restart: unless-stopped
    environment:
      POSTGRES_DB: symfony_db
      POSTGRES_USER: symfony_user
      POSTGRES_PASSWORD: symfony_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"

volumes:
  postgres_data:
```

#### Development Override (compose.override.yaml)

Create your `compose.override.yaml` file for development:

```yaml
services:
  # Development-specific PostgreSQL service configuration
  postgres:
    environment:
      # Override default settings for development
      POSTGRES_DB: symfony_db_dev
      POSTGRES_USER: dev_user
      POSTGRES_PASSWORD: dev_password
    ports:
      # Expose PostgreSQL port for external access during development
      - "5432:5432"
    volumes:
      # Use named volume for development data persistence
      - postgres_dev_data:/var/lib/postgresql/data

  # Development web service overrides
  web:
    environment:
      # Development DATABASE_URL
      - DATABASE_URL=postgresql://dev_user:dev_password@postgres:5432/symfony_db_dev?serverVersion=15&charset=utf8
      # Additional development environment variables
      - APP_ENV=dev
      - APP_DEBUG=true
    volumes:
      # Additional development volumes for hot reloading
      - ./Web:/var/www/html:cached

  # Optional: pgAdmin for database management in development
  pgadmin:
    image: dpage/pgadmin4:latest
    restart: unless-stopped
    environment:
      PGADMIN_DEFAULT_EMAIL: admin@example.com
      PGADMIN_DEFAULT_PASSWORD: admin
    ports:
      - "8080:80"
    depends_on:
      - postgres
    volumes:
      - pgadmin_data:/var/lib/pgladmin

volumes:
  postgres_dev_data:
  pgadmin_data:
```

### 4. Update Dockerfile for PostgreSQL Support

Update your Dockerfile to include PostgreSQL extensions:

```dockerfile
FROM php:8.2-apache

# Install PostgreSQL dependencies
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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Default command
CMD ["apache2-foreground"]
```

### 5. Environment Configuration

Create or update your `.env` file:

```bash
# Update .env file with PostgreSQL configuration
echo "DATABASE_URL=postgresql://dev_user:dev_password@postgres:5432/symfony_db_dev?serverVersion=15&charset=utf8" >> .env

# Set environment
echo "APP_ENV=dev" >> .env
echo "APP_DEBUG=true" >> .env
```

### 6. Docker Commands

```bash
# Build and start services
docker-compose up -d --build

# Check services status
docker-compose ps

# View logs
docker-compose logs postgres
docker-compose logs web

# Stop services
docker-compose down

# Stop services and remove volumes (careful - this deletes data!)
docker-compose down -v
```

### 7. Database Initialization and Migration

```bash
# Access the web container
docker-compose exec web bash

# Inside the container, run Symfony commands:

# Create database (if not exists)
php bin/console doctrine:database:create

# Generate migration files
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Optional: Load fixtures (if you have any)
php bin/console doctrine:fixtures:load
```

### 8. Verification Commands

```bash
# Test PostgreSQL connection
docker-compose exec postgres psql -U dev_user -d symfony_db_dev -c "\l"

# Check if ORM is properly configured
docker-compose exec web php bin/console doctrine:schema:validate

# List all Doctrine commands
docker-compose exec web php bin/console list doctrine

# Check database connection from Symfony
docker-compose exec web php bin/console dbal:run-sql "SELECT version();"
```

### 9. Database Management Tools

#### Access pgAdmin (if enabled)
- URL: http://localhost:8080
- Email: admin@example.com
- Password: admin

#### Direct PostgreSQL access
```bash
# Connect to PostgreSQL directly
docker-compose exec postgres psql -U dev_user -d symfony_db_dev

# Common PostgreSQL commands:
\l          # List databases
\c dbname   # Connect to database
\dt         # List tables
\d table    # Describe table
\q          # Quit
```

### 10. Troubleshooting Commands

```bash
# Check container logs
docker-compose logs -f postgres
docker-compose logs -f web

# Restart specific service
docker-compose restart postgres

# Recreate containers
docker-compose up -d --force-recreate

# Check network connectivity
docker-compose exec web ping postgres

# Test database connection
docker-compose exec web php -r "
try {
    \$pdo = new PDO('pgsql:host=postgres;dbname=symfony_db_dev', 'dev_user', 'dev_password');
    echo 'Connection successful!\n';
} catch (Exception \$e) {
    echo 'Connection failed: ' . \$e->getMessage() . '\n';
}
"
```

### 11. Production Considerations

For production deployment, create a separate `compose.prod.yaml`:

```bash
# Use production compose file
docker-compose -f compose.yaml -f compose.prod.yaml up -d

# Example production overrides in compose.prod.yaml:
# - Remove pgAdmin service
# - Use environment variables for sensitive data
# - Configure proper restart policies
# - Use external volumes for data persistence
# - Set up proper logging
```

## Migration from MySQL to PostgreSQL

If migrating from an existing MySQL setup:

```bash
# 1. Export MySQL data
docker-compose exec mysql mysqldump -u root -p database_name > backup.sql

# 2. Convert MySQL dump to PostgreSQL format (manual or use tools like pgloader)

# 3. Import to PostgreSQL
docker-compose exec postgres psql -U dev_user -d symfony_db_dev < converted_backup.sql

# 4. Update all PDO connections in your PHP code to use PostgreSQL syntax
```

## Summary

After running these commands, you will have:
- ✅ Symfony ORM Pack installed
- ✅ PostgreSQL service running in Docker
- ✅ Development environment configured
- ✅ Database tools available (pgAdmin)
- ✅ Working database connection
- ✅ Migration system ready

## Quick Start Commands Summary

```bash
# Install dependencies
composer require symfony/orm-pack

# Start services
docker-compose up -d --build

# Initialize database
docker-compose exec web php bin/console doctrine:database:create
docker-compose exec web php bin/console doctrine:migrations:migrate

# Verify setup
docker-compose exec web php bin/console doctrine:schema:validate
```

## Support and Documentation

- [Symfony ORM Pack Documentation](https://symfony.com/doc/current/doctrine.html)
- [PostgreSQL Docker Image](https://hub.docker.com/_/postgres)
- [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html)
- [Docker Compose Documentation](https://docs.docker.com/compose/)