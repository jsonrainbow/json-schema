# JSON Schema for PHP [![Build Status](https://secure.travis-ci.org/justinrainbow/json-schema.png)](http://travis-ci.org/justinrainbow/json-schema)

A PHP Implementation for validating `JSON` Structures against a given `Schema`.

See [json-schema](http://json-schema.org/) for more details.

## Installation

### Library

    $ git clone https://github.com/justinrainbow/json-schema.git

### Dependencies

#### via `submodules` (*will use the Symfony ClassLoader Component*)

    $ git submodule update --init

#### via [`composer`](https://github.com/composer/composer) (*will use the Composer ClassLoader*)

    $ wget http://getcomposer.org/composer.phar
    $ php composer.phar install

## Usage

```php
<?php

$validator = new JsonSchema\Validator();
$validator->check(json_decode($json), json_decode($schema));

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n",$error['property'], $error['message']);
    }
}
```

## Running the tests

    $ phpunit
