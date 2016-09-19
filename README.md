# JSON Schema for PHP

[![Build Status](https://travis-ci.org/justinrainbow/json-schema.svg?branch=master)](https://travis-ci.org/justinrainbow/json-schema)
[![Latest Stable Version](https://poser.pugx.org/justinrainbow/json-schema/v/stable.png)](https://packagist.org/packages/justinrainbow/json-schema)
[![Total Downloads](https://poser.pugx.org/justinrainbow/json-schema/downloads.png)](https://packagist.org/packages/justinrainbow/json-schema)

A PHP Implementation for validating `JSON` Structures against a given `Schema`.

See [json-schema](http://json-schema.org/) for more details.

## Installation

### Library

    $ git clone https://github.com/justinrainbow/json-schema.git

### Dependencies

#### [`Composer`](https://github.com/composer/composer) (*will use the Composer ClassLoader*)

    $ wget http://getcomposer.org/composer.phar
    $ php composer.phar require --no-update justinrainbow/json-schema
    $ composer.phar update

## Usage

```php
<?php

$data = json_decode(file_get_contents('data.json')); //NOTE: this needs to be an object, not an array!

// Validate
$validator = new JsonSchema\Validator;
$validator->check($data, (object)['$ref' => 'file://' . realpath('schema.json')]); //either provide PHY schema (after json_decode) or just point out to the correct schema on the local filesystem

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n", $error['property'], $error['message']);
    }
}
```

## Running the tests

    $ vendor/bin/phpunit
