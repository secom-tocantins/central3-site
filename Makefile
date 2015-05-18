VERSION	   = 0.1.13
SHELL		:= $(shell which bash)
.SHELLFLAGS = -c

.SILENT: ;			   # no need for @
.ONESHELL: ;			 # recipes execute in same shell
.NOTPARALLEL: ;		  # wait for this target to finish
.EXPORT_ALL_VARIABLES: ; # send all vars to shell
default: help-default;   # default target
Makefile: ;			  # skip prerequisite discovery

help-default help:
	@echo "======================================================="
	@echo "					Options"
	@echo "======================================================="
	@echo "         setup: Configure the project in this machine"
	@echo "        server: Execute with php webserver"
	@echo ""

server:
	php -S 127.0.0.1:8080 -t public/ &
	sensible-browser http://127.0.0.1:8080

setup:
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install --dev --prefer-dist

docker-build:
	docker build -t ubuntu-dev .

docker-console:
	docker run -v $(realpath .):/www -it --name silex-console --rm ubuntu-dev

docker-server:
	docker run -p 8000:8000 -v $(realpath .):/www -it --name silex-server --rm ubuntu-dev php -S 0.0.0.0:8000 -t /www/public/
