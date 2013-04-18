# JSON Schema for PHP [![Build Status](https://secure.travis-ci.org/justinrainbow/json-schema.png)](http://travis-ci.org/justinrainbow/json-schema)

A PHP Implementation for validating `JSON` Structures against a given `Schema`.

See [json-schema](http://json-schema.org/) for more details.

## Installation

### Library

	git clone git://github.com/Hypercharge/json-schema.git

### Dependencies

#### [`Composer`](https://github.com/composer/composer) (*will use the Composer ClassLoader*)

	wget http://getcomposer.org/composer.phar
	php composer.phar install

Or if you don't have wget you can do same with curl

	curl -o composer.phar http://getcomposer.org/composer.phar
	php composer.phar install

## Usage

```php
<?php

// Get the schema and data as objects
$data = json_decode(file_get_contents('data.json'));
$schemaUri = 'file://' . realpath('schema.json');

$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve($schemaUri);

// If you use $ref or if you are unsure, resolve those references here
// This modifies the $schema object
$refResolver = new JsonSchema\RefResolver($retriever);
$refResolver->resolve($schema, $schemaUri);

// Validate
$validator = new JsonSchema\Validator();
$validator->check($data, $schema);

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n", $error['property'], $error['message']);
    }
}
```

## Tests

Uses https://github.com/Julian/JSON-Schema-Test-Suite as well as our own. You'll need to update and init the git submodules:

	git submodule update --init

install phpunit

	php composer.phar install --dev

run tests

	./vendor/bin/phpunit.php


install the testserver - install [node.js](http://nodejs.org/) first

	cd testserver
	npm install

start the testserver

	node testserver/server.js

