.PHONY: help build up down restart logs clean dev prod backup restore status test test-unit test-integration test-performance test-security test-e2e test-all

# Default target
help: ## Show this help message
	@echo "Family Tree Docker Management & Testing"
	@echo ""
	@echo "Available commands:"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Development commands
dev: ## Start development environment with all services
	docker-compose --profile dev up -d
	@echo "Development environment started!"
	@echo "Family Tree: http://localhost:8080"
	@echo "Adminer:     http://localhost:8081"
	@echo "MailHog:     http://localhost:8025"
	@echo "PHPMyAdmin:  http://localhost:8082"

up: ## Start production environment
	docker-compose up -d
	@echo "Production environment started!"
	@echo "Family Tree: http://localhost:8080"

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

build: ## Build custom images
	docker-compose build --no-cache

# Monitoring commands
logs: ## Show logs from all services
	docker-compose logs -f

logs-%: ## Show logs from specific service (e.g., make logs-wordpress)
	docker-compose logs -f $*

status: ## Show status of all services
	docker-compose ps

health: ## Check health of all services
	@echo "Checking service health..."
	@docker-compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
	@echo ""
	@echo "Testing endpoints..."
	@curl -s -o /dev/null -w "Nginx: %{http_code}\n" http://localhost:8080/health || echo "Nginx: Failed"
	@curl -s -o /dev/null -w "WordPress: %{http_code}\n" http://localhost:8080/wp-admin/install.php || echo "WordPress: Failed"

# Testing commands
test: ## Run all tests
	./run-tests.sh

test-unit: ## Run unit tests only
	@if command -v phpunit >/dev/null 2>&1; then \
		phpunit --configuration phpunit.xml --testsuite Unit; \
	elif [ -f "vendor/bin/phpunit" ]; then \
		vendor/bin/phpunit --configuration phpunit.xml --testsuite Unit; \
	else \
		echo "PHPUnit not found. Install with: composer require --dev phpunit/phpunit"; \
	fi

test-integration: ## Run integration tests only
	@if command -v phpunit >/dev/null 2>&1; then \
		phpunit --configuration phpunit.xml --testsuite Integration; \
	elif [ -f "vendor/bin/phpunit" ]; then \
		vendor/bin/phpunit --configuration phpunit.xml --testsuite Integration; \
	else \
		echo "PHPUnit not found. Install with: composer require --dev phpunit/phpunit"; \
	fi

test-performance: ## Run performance tests
	@if command -v wp >/dev/null 2>&1; then \
		wp eval-file tests/performance/performance-test.php --allow-root; \
	else \
		echo "WP-CLI not found. Install WP-CLI to run performance tests."; \
	fi

test-security: ## Run security tests
	@if command -v wp >/dev/null 2>&1; then \
		wp eval-file tests/security/security-test.php --allow-root; \
	else \
		echo "WP-CLI not found. Install WP-CLI to run security tests."; \
	fi

test-e2e: ## Run end-to-end tests
	@if command -v wp >/dev/null 2>&1; then \
		wp eval-file tests/e2e/e2e-test.php --allow-root; \
	else \
		echo "WP-CLI not found. Install WP-CLI to run E2E tests."; \
	fi

test-syntax: ## Check PHP syntax
	@echo "Checking PHP syntax..."
	@docker-compose exec -T wordpress find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec php -l {} \;

test-frontend: ## Run frontend tests (if configured)
	@if [ -f "package.json" ] && command -v npm >/dev/null 2>&1; then \
		npm test; \
	else \
		echo "Frontend tests not configured or npm not available."; \
	fi

# Cleanup commands
clean: ## Remove all containers, volumes, and images
	docker-compose down -v --rmi all

clean-test-data: ## Clean up test data from database
	@echo "Cleaning up test data..."
	@docker-compose exec mysql mysql -u familytree -pchangeme123 familytree -e "DELETE FROM wp_family_members WHERE first_name LIKE 'Test%' OR first_name LIKE 'PerfTest%' OR first_name LIKE 'E2E%';"

# Deployment helpers
backup: ## Create database backup
	@echo "Creating database backup..."
	@docker-compose exec mysql mysqldump -u familytree -pchangeme123 familytree > backup_$(date +%Y%m%d_%H%M%S).sql

restore: ## Restore database from backup (usage: make restore FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "Usage: make restore FILE=backup_file.sql"; \
		exit 1; \
	fi
	@echo "Restoring database from $(FILE)..."
	@docker-compose exec -T mysql mysql -u familytree -pchangeme123 familytree < $(FILE)

# Database commands
db-connect: ## Connect to MySQL database
	docker-compose exec mysql mysql -u $(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)

db-backup: ## Create database backup
	@echo "Creating database backup..."
	docker-compose exec mysql mysqldump -u root -p$(MYSQL_ROOT_PASSWORD) $(DB_NAME) > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Backup created: backup_$(shell date +%Y%m%d_%H%M%S).sql"

db-restore: ## Restore database from backup file (usage: make db-restore FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then echo "Usage: make db-restore FILE=backup.sql"; exit 1; fi
	@echo "Restoring database from $(FILE)..."
	docker-compose exec -T mysql mysql -u root -p$(MYSQL_ROOT_PASSWORD) $(DB_NAME) < $(FILE)
	@echo "Database restored!"

# WordPress commands
wp-install: ## Install WordPress
	docker-compose exec wordpress wp core install --url=http://localhost:8080 --title="Family Tree" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --allow-root

wp-plugins: ## Install and activate required plugins
	docker-compose exec wordpress wp plugin install redis-cache --activate --allow-root
	docker-compose exec wordpress wp plugin activate family-tree --allow-root
	docker-compose exec wordpress wp redis enable --allow-root

wp-theme: ## Install a basic theme
	docker-compose exec wordpress wp theme install twentytwentythree --activate --allow-root

# Maintenance commands
clean: ## Remove all containers, volumes, and images
	docker-compose down -v --rmi all

clean-volumes: ## Remove all volumes (WARNING: This will delete data!)
	@echo "WARNING: This will permanently delete all data!"
	@read -p "Are you sure? [y/N] " confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		docker-compose down -v; \
		echo "All volumes removed."; \
	else \
		echo "Operation cancelled."; \
	fi

update: ## Update all images and restart services
	docker-compose pull
	docker-compose up -d

# Development helpers
shell: ## Open shell in WordPress container
	docker-compose exec wordpress bash

shell-mysql: ## Open MySQL shell
	docker-compose exec mysql mysql -u root -p

shell-redis: ## Open Redis shell
	docker-compose exec redis redis-cli

# Testing commands
test: ## Run tests (if available)
	docker-compose exec wordpress ./vendor/bin/phpunit

lint: ## Run PHP linting
	docker-compose exec wordpress find . -name "*.php" -exec php -l {} \;

# Production deployment
prod-deploy: ## Deploy to production (requires production docker-compose.prod.yml)
	@echo "Deploying to production..."
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

prod-logs: ## View production logs
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# Utility commands
env-check: ## Check if .env file exists and show configuration
	@if [ ! -f .env ]; then \
		echo "ERROR: .env file not found!"; \
		echo "Copy .env.example to .env and configure your settings."; \
		exit 1; \
	fi
	@echo "Environment configuration:"
	@grep -v '^#' .env | grep -v '^$$'

setup: ## Initial setup for new installation
	@echo "Setting up Family Tree Docker environment..."
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "Created .env file from template. Please edit with your settings."; \
	fi
	@echo "Building and starting services..."
	make build
	make up
	@echo "Waiting for services to be ready..."
	sleep 30
	make health
	@echo ""
	@echo "Setup complete! Access your site at http://localhost:8080"
	@echo "Don't forget to run 'make wp-install' to set up WordPress."</content>
