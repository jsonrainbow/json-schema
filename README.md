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
    $ php composer.phar require justinrainbow/json-schema:~1.3

## Usage

```php
<?php

// Get the schema and data as objects
$retriever = new JsonSchema\Uri\UriRetriever;
$schema = $retriever->retrieve('file://' . realpath('schema.json'));
$data = json_decode(file_get_contents('data.json'));

// If you use $ref or if you are unsure, resolve those references here
// This modifies the $schema object
$refResolver = new JsonSchema\RefResolver($retriever);
$refResolver->resolve($schema, 'file://' . __DIR__);

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

## Custom Constraints
Add custom constraints via `$validator->addConstraint($name, $constraint)`;

The given `$constraint` is applied when `$name` is found within the current evaluated schema path.

### Add a callable constraint

	$validator->addConstraint('test', \Callable);

 * Inherits _current_ ctr params (uriRetriever, factory)
 * The callable is the `ConstraintInterface->check` signature `function check($value, $schema = null, $path = null, $i = null)`

### Add by custom Constraint instance
	
	$validator->addConstraint('test', new MyCustomConstraint(...));

 * Requires adding the correct ctr params (uriRetriever, factory et al)
 * `MyCustomConstraint` must be of type `JsonSchema\Constraints\ConstraintInterface`

### Add by custom Constraint class-name

	$validator->addConstraint('test', 'FQCN');

 * Inherits _current_ ctr params (uriRetriever, factory)
 * `FQCN` must be of type `JsonSchema\Constraints\ConstraintInterface`

## Running the tests

    $ vendor/bin/phpunit
