-include .env
-include .env.local
-include tools/.colors

user	:= $(shell id -u)
group	:= $(shell id -g)

dc	:= USER_ID=$(user) GROUP_ID=$(group) docker-compose -f docker-compose.$(APP_ENV).yaml -p $(APP_DIR)_$(APP_ENV) --env-file .env
dr	:= $(dc) run --rm
de	:= $(dc) exec

njs := $(dr) node
php	:= $(dr) --no-deps php
sy	:= $(php) php bin/console

help:
	@echo "################################"
	@echo "# HELP"
	@echo "################################"
	@$(call cyan,"docker-build") : ***
	@$(call cyan,"doctrine/database/create") : ***
	@$(call cyan,"server-down") : ***
	@$(call cyan,"server-restart") : ***
	@$(call cyan,"server-start") : ***
	@$(call cyan,"server-stop") : ***
	@$(call cyan,"vendor/autoload.php") : ***

docker-build:
	@echo "################################"
	@echo "# docker-build"
	@echo "################################"
	$(dc) pull --ignore-pull-failures
	$(dc) build
	docker build -t $(APP_DIR)_$(APP_ENV)_node:latest ./docker/node/

doctrine/database/create:
	@echo "################################"
	@echo "# doctrine/database/create"
	@echo "################################"
	$(sy) doctrine:database:create -c mysql --if-not-exists
	$(sy) doctrine:database:create -c local
	$(sy) doctrine:schema:update --force --em mysql

env/dev:
	@echo "################################"
	@echo "# env/dev"
	@echo "################################"
	rm .env -f
	rm ./app/.env -f
	cat env/.env >> .env
	cat env/.env.dev >> .env
	@bash env/app.sh

env/local:
	@echo "################################"
	@echo "# env/local"
	@echo "################################"
	rm .env -f
	rm ./app/.env -f
	cat env/.env >> .env
	cat env/.env.local >> .env
	@bash env/app.sh

env/prod:
	@echo "################################"
	@echo "# env/prod"
	@echo "################################"
	rm .env -f
	rm ./app/.env -f
	cat env/.env >> .env
	cat env/.env.prod >> .env
	cat env/.env.prod.github >> .env
	@bash env/app.sh

public/assets:
	@echo "################################"
	@echo "# public/assets"
	@echo "################################"
	$(njs) yarn
	$(njs) yarn run build

public/assets/dev:
	@echo "################################"
	@echo "# public/assets/dev"
	@echo "################################"
	$(node) yarn
	$(node) yarn run dev

server-down:
	@echo "################################"
	@echo "# server-down"
	@echo "################################"
	$(dc) down
	docker volume prune -f
	docker network prune -f

server-restart:
	@echo "################################"
	@echo "# server-restart"
	@echo "################################"
	$(dc) restart

server-start:
	@echo "################################"
	@echo "# server-start"
	@echo "################################"
	$(dc) up -d

server-stop:
	@echo "################################"
	@echo "# server-stop"
	@echo "################################"
	$(dc) stop

swagger:
	@echo "################################"
	@echo "# swagger"
	@echo "################################"
	$(php) ./vendor/bin/openapi --format json --output ./swagger.json ./config/swagger.php ./src/

vendor/autoload.php: app/composer.lock
	@echo "################################"
	@echo "# vendor/autoload.php"
	@echo "################################"
	$(php) composer update