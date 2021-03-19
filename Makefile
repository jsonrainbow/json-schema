phpcs: vendor
	vendor/bin/phpcs -s --standard=PSR12 --extensions=php bin demo src tests

vendor: composer.json
	composer update
	touch $@
