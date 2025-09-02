#!/bin/bash

# Quick Start Script for PostgreSQL + Symfony ORM Pack Installation
# This script automates the installation and setup process

set -e  # Exit on any error

echo "🚀 Starting PostgreSQL + Symfony ORM Pack Setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Check if composer is available (either local or via Docker)
if ! command -v composer &> /dev/null; then
    print_warning "Composer not found locally. Will use Docker Composer."
    COMPOSER_CMD="docker run --rm -v \$(pwd):/app composer"
else
    COMPOSER_CMD="composer"
fi

print_status "Installing Symfony ORM Pack..."
eval "$COMPOSER_CMD require symfony/orm-pack"

print_status "Installing additional PostgreSQL dependencies..."
eval "$COMPOSER_CMD require doctrine/dbal"

print_status "Copying environment configuration..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_status "Created .env file from template"
else
    print_warning ".env file already exists, skipping copy"
fi

print_status "Building and starting Docker services..."
docker-compose up -d --build

# Wait for PostgreSQL to be ready
print_status "Waiting for PostgreSQL to be ready..."
sleep 10

# Check if containers are running
if docker-compose ps | grep -q "Up"; then
    print_status "Docker services are running"
else
    print_error "Some Docker services failed to start"
    docker-compose logs
    exit 1
fi

print_status "Checking PostgreSQL connection..."
if docker-compose exec -T postgres pg_isready -U dev_user -d symfony_db_dev; then
    print_status "PostgreSQL is ready"
else
    print_error "PostgreSQL is not ready"
    exit 1
fi

print_status "Setup completed successfully! 🎉"

echo ""
echo "📋 Quick Access Information:"
echo "   🌐 Web Application: http://localhost"
echo "   🗄️ pgAdmin: http://localhost:8080"
echo "      Email: admin@example.com"
echo "      Password: admin"
echo "   🐘 PostgreSQL: localhost:5432"
echo "      User: dev_user"
echo "      Password: dev_password"
echo "      Database: symfony_db_dev"
echo ""
echo "📚 Next Steps:"
echo "   1. Initialize database: docker-compose exec web php bin/console doctrine:database:create"
echo "   2. Run migrations: docker-compose exec web php bin/console doctrine:migrations:migrate"
echo "   3. Check setup: docker-compose exec web php bin/console doctrine:schema:validate"
echo ""
echo "📖 For detailed documentation, see: POSTGRESQL_SYMFONY_INSTALLATION.md"