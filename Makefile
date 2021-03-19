phpstan: vendor-bin/phpstan/vendor
	vendor/bin/phpstan analyse

phpstan-baseline: vendor-bin/phpstan/vendor
	vendor/bin/phpstan analyse --generate-baseline

vendor-bin/phpstan/vendor: vendor vendor-bin/phpstan/composer.json
	composer bin phpstan update
	touch $@

vendor: composer.json
	composer update
	touch $@
