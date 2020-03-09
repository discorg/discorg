build: vendor

vendor:
	composer install --no-interaction

all: vendor lint check-code-style phpstan deptrac tests behat

.PHONY: lint
lint:
	vendor/bin/parallel-lint -e php --exclude vendor .

.PHONY: check-code-style
check-code-style:
	vendor/bin/phpcs

.PHONY: fix-code-style
fix-code-style:
	vendor/bin/phpcbf

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: deptrac
deptrac:
	vendor/bin/deptrac --no-cache

.PHONY: tests
tests:
	vendor/bin/phpunit

.PHONY: behat
behat:
	vendor/bin/behat

.PHONY: run
run:
	php -S localhost:8000 -t public/

.PHONY: dependencies-to-update
dependencies-to-update:
	composer show --direct --outdated
