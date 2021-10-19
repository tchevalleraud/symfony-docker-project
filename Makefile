-include .env
-include .env.local
-include tools/.colors

user	:= $(shell id -u)
group	:= $(shell id -g)

dc	:= USER_ID=$(user) GROUP_ID=$(group) docker-compose -f docker-compose.$(APP_ENV).yaml -p $(APP_NAME)_$(APP_ENV) --env-file .env
dr	:= $(dc) run --rm
de	:= $(dc) exec

php	:= $(dr) --no-deps php
sy	:= $(php) php bin/console

help:
	@echo "################################"
	@echo "# HELP"
	@echo "################################"
	@$(call cyan,"docker-build") : ***
	@$(call cyan,"server-down") : ***
	@$(call cyan,"server-restart") : ***
	@$(call cyan,"server-start") : ***
	@$(call cyan,"server-stop") : ***

docker-build:
	@echo "################################"
	@echo "# docker-build"
	@echo "################################"
	$(dc) pull --ignore-pull-failures
	$(dc) build

doctrine/database/create:
	@echo "################################"
	@echo "# doctrine/database/create"
	@echo "################################"
	$(sy) doctrine:database:create -c mysql --if-not-exists
	$(sy) doctrine:database:create -c local
	$(sy) doctrine:schema:update --force --em mysql

server-down:
	@echo "################################"
	@echo "# server-down"
	@echo "################################"
	$(dc) down

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

vendor/autoload.php: app/composer.lock
	@echo "################################"
	@echo "# vendor/autoload.php"
	@echo "################################"
	$(php) composer update