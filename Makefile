build: vendor

vendor:
	composer install --no-interaction

all: vendor lint check-code-style

.PHONY: lint
lint:
	vendor/bin/parallel-lint -e php --exclude vendor .

.PHONY: check-code-style
check-code-style:
	vendor/bin/phpcs

.PHONY: fix-code-style
fix-code-style:
	vendor/bin/phpcbf
