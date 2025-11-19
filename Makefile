# Docker settings
DOCKER_NETWORK=promobank
SERVICES=    auth-service media-service promo-service telegram-service  web-service  game-service  notification-service
# notification-service payment-service  profile-service    
INFRA_COMPOSE=docker-compose/infrastructure.yml 

# Helper
.PHONY: helpmake
help:
	@echo ""
	@echo " NETWORK & PGADMIN:"
	@echo "  make network                        # Create Docker network if not exists"
	@echo "  make pgadmin                        # Start pgAdmin container"
	@echo ""
	@echo " BUILD:"
	@echo "  make build                          # Build all services"
	@echo "  make build s=\"promo-service auth-service\""
	@echo ""
	@echo " UP:"
	@echo "  make up                             # Run all services"
	@echo "  make up s=\"api-getaway\""
	@echo ""
	@echo " MIGRATE:"
	@echo "  make migrate                        # Migrate all services"
	@echo "  make migrate s=\"promo-service auth-service\""
	@echo ""
	@echo "璘 DOWN:"
	@echo "  make down                           # Stop all services"
	@echo "  make down s=\"auth-service\""
	@echo "  make down s=\"api-getaway promo-service\""
	@echo ""
	@echo "♻️ RESTART:"
	@echo "  make restart                        # Restart all services (down -> build -> up)"
	@echo "  make restart s=\"game-service\"      # Restart specific service(s)"
	@echo ""
	@echo " KEY:"
	@echo "  make key s=\"service-name\"          # Generate Laravel APP_KEY for a service"
	@echo ""
	@echo " OPTIMIZE:"
	@echo "  make optimize                       # Optimize all Laravel services"
	@echo "  make optimize s=\"auth-service\"     # Optimize specific service"
	@echo ""
	@echo "藺 CUSTOM COMMAND:"
	@echo "  make run s=service c='command'      # Run any command inside a service (artisan, composer, bash...)"
	@echo "  make run-all c='command'            # Run a command in all services"
	@echo "                                      # Example: make run s=\"auth-service\" c=\"php artisan migrate\""
# Create Docker network if not exists
.PHONY: network
network:
	@if ! docker network inspect $(DOCKER_NETWORK) > /dev/null 2>&1; then \
		echo "Creating Docker network: $(DOCKER_NETWORK)"; \
		docker network create $(DOCKER_NETWORK); \
	else \
		echo "✅ Docker network '$(DOCKER_NETWORK)' already exists."; \
	fi
.PHONY: fix-permissions
fix-permissions:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Fixing permissions for $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container bash -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Fixing permissions for $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container bash -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"; \
		done; \
	fi
# Start pgAdmin
.PHONY: docker-global
docker-global:
	docker compose -f $(INFRA_COMPOSE) up -d


.PHONY: rebuild
rebuild:
	@if [ -z "$(s)" ]; then \
		echo "❌ Please specify service(s) with: make rebuild s=\"service-name\""; \
	else \
		for service in $(s); do \
			echo "♻️ Rebuilding $$service..."; \
			docker compose -f $$service/docker-compose.yml build; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	fi
# Build services
.PHONY: build
build:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo " Building $$service..."; \
			docker compose -f $$service/docker-compose.yml build; \
		done; \
	else \
		for service in $(s); do \
			echo " Building $$service..."; \
			docker compose -f $$service/docker-compose.yml build; \
		done; \
	fi

# Up services
.PHONY: up
up:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo " Starting $$service..."; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	else \
		for service in $(s); do \
			echo " Starting $$service..."; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	fi

# Migrate Laravel projects
# Migrate + Seed Laravel projects
.PHONY: migrate
migrate:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Migrating & Seeding $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate --seed || { echo "❌ Migration failed for $$service"; exit 1; }; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Migrating & Seeding $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate --seed || { echo "❌ Migration failed for $$service"; exit 1; }; \
		done; \
	fi

.PHONY: fresh
fresh:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo "粒 Fresh migrating & seeding $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate:fresh --seed || { echo "❌ Fresh migration failed for $$service"; exit 1; }; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo "粒 Fresh migrating & seeding $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate:fresh --seed || { echo "❌ Fresh migration failed for $$service"; exit 1; }; \
		done; \
	fi
# Down services
.PHONY: down
down:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo "璘 Stopping $$service..."; \
			docker compose -f $$service/docker-compose.yml down; \
		done; \
	else \
		for service in $(s); do \
			echo "璘 Stopping $$service..."; \
			docker compose -f $$service/docker-compose.yml down; \
		done; \
	fi

# Restart services: down -> build -> up
.PHONY: restart
restart:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo "♻️ Restarting $$service..."; \
			docker compose -f $$service/docker-compose.yml down; \
			docker compose -f $$service/docker-compose.yml build; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	else \
		for service in $(s); do \
			echo "♻️ Restarting $$service..."; \
			docker compose -f $$service/docker-compose.yml down; \
			docker compose -f $$service/docker-compose.yml build; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	fi

# Generate Laravel APP_KEY for service(s)
.PHONY: key
key:
	@if [ -z "$(s)" ]; then \
		echo "❌ Please specify the service with: make key s=\"service-name\""; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Generating APP_KEY for $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan key:generate; \
		done; \
	fi

# Optimize Laravel projects
.PHONY: optimize
optimize:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Optimizing $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan optimize; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo " Optimizing $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan optimize; \
		done; \
	fi
# Run any custom command inside a service container
.PHONY: run
run:
	@if [ -z "$(s)" ] || [ -z "$(c)" ]; then \
		echo "❌ Usage: make run s=\"service1 service2\" c='your-command'"; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo "⚙️ Running command in $$service (container: $$app_container): $(c)"; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container sh -c '$(c)'; \
		done; \
	fi
x.PHONY: run-all
run-all:
	@if [ -z "$(c)" ]; then \
		echo "❌ Please provide a command to run in all services:"; \
		echo "   make run-all c=\"php artisan migrate\""; \
	else \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-getaway/_app/')"; \
			echo "▶️ Running in $$service (container: $$app_container): $(c)"; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container sh -c '$(c)'; \
		done; \
	fi