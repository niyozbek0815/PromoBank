# Docker settings
DOCKER_NETWORK=promobank
SERVICES=api-gateway auth-service promo-service game-service payment-service notification-service web-service media-service vote-service
PGADMIN_COMPOSE=docker/pgadmin/docker-compose.pgadmin.yml

# Helper
.PHONY: help
help:
	@echo ""
	@echo "🔧 NETWORK & PGADMIN:"
	@echo "  make network                        # Create Docker network if not exists"
	@echo "  make pgadmin                        # Start pgAdmin container"
	@echo ""
	@echo "🔨 BUILD:"
	@echo "  make build                          # Build all services"
	@echo "  make build s=\"promo-service auth-service\""
	@echo ""
	@echo "🚀 UP:"
	@echo "  make up                             # Run all services"
	@echo "  make up s=\"api-gateway\""
	@echo ""
	@echo "📦 MIGRATE:"
	@echo "  make migrate                        # Migrate all services"
	@echo "  make migrate s=\"promo-service auth-service\""
	@echo ""
	@echo "🧯 DOWN:"
	@echo "  make down                           # Stop all services"
	@echo "  make down s=\"auth-service\""
	@echo "  make down s=\"api-gateway promo-service\""
	@echo ""
	@echo "♻️ RESTART:"
	@echo "  make restart                        # Restart all services (down -> build -> up)"
	@echo "  make restart s=\"game-service\"      # Restart specific service(s)"
	@echo ""
	@echo "🔑 KEY:"
	@echo "  make key s=\"service-name\"          # Generate Laravel APP_KEY for a service"
	@echo ""
	@echo "🚀 OPTIMIZE:"
	@echo "  make optimize                       # Optimize all Laravel services"
	@echo "  make optimize s=\"auth-service\"     # Optimize specific service"
	@echo ""

# Create Docker network if not exists
.PHONY: network
network:
	@if ! docker network inspect $(DOCKER_NETWORK) > /dev/null 2>&1; then \
		echo "Creating Docker network: $(DOCKER_NETWORK)"; \
		docker network create $(DOCKER_NETWORK); \
	else \
		echo "✅ Docker network '$(DOCKER_NETWORK)' already exists."; \
	fi

# Start pgAdmin
.PHONY: pgadmin
pgadmin:
	docker compose -f $(PGADMIN_COMPOSE) up -d

# Build services
.PHONY: build
build:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo "🔨 Building $$service..."; \
			docker compose -f $$service/docker-compose.yml build; \
		done; \
	else \
		for service in $(s); do \
			echo "🔨 Building $$service..."; \
			docker compose -f $$service/docker-compose.yml build; \
		done; \
	fi

# Up services
.PHONY: up
up:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo "🚀 Starting $$service..."; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	else \
		for service in $(s); do \
			echo "🚀 Starting $$service..."; \
			docker compose -f $$service/docker-compose.yml up -d; \
		done; \
	fi

# Migrate Laravel projects
.PHONY: migrate
migrate:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-gateway/_app/')"; \
			echo "📦 Migrating $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-gateway/_app/')"; \
			echo "📦 Migrating $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan migrate; \
		done; \
	fi

# Down services
.PHONY: down
down:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			echo "🧯 Stopping $$service..."; \
			docker compose -f $$service/docker-compose.yml down; \
		done; \
	else \
		for service in $(s); do \
			echo "🧯 Stopping $$service..."; \
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
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-gateway/_app/')"; \
			echo "🔑 Generating APP_KEY for $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan key:generate; \
		done; \
	fi

# Optimize Laravel projects
.PHONY: optimize
optimize:
	@if [ -z "$(s)" ]; then \
		for service in $(SERVICES); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-gateway/_app/')"; \
			echo "🚀 Optimizing $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan optimize; \
		done; \
	else \
		for service in $(s); do \
			app_container="$$(basename $$service | sed 's/-service/_app/' | sed 's/-gateway/_app/')"; \
			echo "🚀 Optimizing $$service (container: $$app_container)..."; \
			docker compose -f $$service/docker-compose.yml exec -T $$app_container php artisan optimize; \
		done; \
	fi