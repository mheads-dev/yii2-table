CLI_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(sort $(subst :,\:,$(CLI_ARGS))):;@:)

COMPOSE=docker compose -f docker/docker-compose.yml $(if $(wildcard docker/docker-compose.override.yml),-f docker/docker-compose.override.yml)

help: ## Show the list of available commands with description.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.DEFAULT_GOAL := help

build: ## Build services
	$(COMPOSE) --profile all build
up: ## Start services
	$(COMPOSE) --profile all up -d
ps: ## List running services
	$(COMPOSE) ps
stop: ## Stop running services
	$(COMPOSE) --profile all stop
down: ## Stop running services and remove containers, networks and volumes
	$(COMPOSE) --profile all down \
	--remove-orphans \
	--volumes
clear: ## Remove all containers, networks, volumes and images
	$(COMPOSE) --profile all down \
	--remove-orphans \
	--volumes \
    --rmi all

run: ## Run arbitrary command
	$(COMPOSE) --profile php run \
	--rm \
	--entrypoint $(CMD) \
	php

php: ## Run php in container. Example: make php PHP_ARGS="-v"
	$(COMPOSE) --profile php run \
	--rm \
	--entrypoint php \
	php $(PHP_ARGS)

test-all: test-unit
test-unit: ## Run demo tests
	$(COMPOSE) exec php \
		vendor/bin/phpunit --testsuite Unit $(RUN_ARGS)

psalm: CMD="vendor/bin/psalm --no-cache" ## Run static analysis using Psalm
psalm: run

cs-fixer: CMD="vendor/bin/php-cs-fixer fix" ## Run code-style fixer
cs-fixer: run

shell: CMD="bash" ## Open interactive shell
shell: run

composer: ## Run Composer.
	$(COMPOSE) --profile php up -d
	$(COMPOSE) exec php composer $(CLI_ARGS)
