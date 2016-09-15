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
    $ php composer.phar require justinrainbow/json-schema:~2.0

## Usage

```php
<?php

$data = json_decode(file_get_contents('data.json'));

// Validate
$validator = new JsonSchema\Validator;
$validator->check($data, (object)['$ref' => 'file://' . realpath('schema.json')]);

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n", $error['property'], $error['message']);
    }
}
```
###Type Coercion
If you're validating data passed to your application via HTTP, you can cast strings and booleans to the expected types defined by your schema:
```
$request = (object)[
   'processRefund'=>"true",
   'refundAmount'=>"17"
];

$validator = new \JsonSchema\Validator(\JsonSchema\Constraints\Constraint::CHECK_MODE_COERCE);
$validator->check($request, (object) [
    "type"=>"object",
    "properties"=>[
        "processRefund"=>[
            "type"=>"boolean"
        ],
        "refundAmount"=>[
            "type"=>"number"
        ]
    ]
]); // validates!

is_bool($request->processRefund); // true
is_int($request->refundAmount); // true
```

## Running the tests

    $ vendor/bin/phpunit
