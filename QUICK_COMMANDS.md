# Quick Command Reference - PostgreSQL + Symfony ORM Pack

## Essential Commands Summary

### 1. Install Dependencies
```bash
composer require symfony/orm-pack
composer require doctrine/dbal
```

### 2. Docker Setup
```bash
# Build and start services
docker-compose up -d --build

# Check status
docker-compose ps

# View logs
docker-compose logs postgres
docker-compose logs web
```

### 3. Database Initialization
```bash
# Create database
docker-compose exec web php bin/console doctrine:database:create

# Generate and run migrations
docker-compose exec web php bin/console make:migration
docker-compose exec web php bin/console doctrine:migrations:migrate

# Validate schema
docker-compose exec web php bin/console doctrine:schema:validate
```

### 4. Quick Access URLs
- Web Application: http://localhost
- pgAdmin: http://localhost:8080 (admin@example.com / admin)
- PostgreSQL: localhost:5432 (dev_user / dev_password)

### 5. Troubleshooting
```bash
# Test PostgreSQL connection
docker-compose exec postgres psql -U dev_user -d symfony_db_dev

# Test database from PHP
docker-compose exec web php -r "
try {
    \$pdo = new PDO('pgsql:host=postgres;dbname=symfony_db_dev', 'dev_user', 'dev_password');
    echo 'Connection successful!\n';
} catch (Exception \$e) {
    echo 'Connection failed: ' . \$e->getMessage() . '\n';
}
"

# Restart services if needed
docker-compose restart
docker-compose down && docker-compose up -d
```

### 6. One-Line Setup (Automated)
```bash
./setup.sh
```

---
For complete documentation see: `POSTGRESQL_SYMFONY_INSTALLATION.md`