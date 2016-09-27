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

### Basic usage

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

### With inline references

```php
<?php

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

$jsonSchema = <<<'JSON'
{
    "type": "object",
    "properties": {
        "data": {
            "oneOf": [
                { "$ref": "#/definitions/integerData" },
                { "$ref": "#/definitions/stringData" }
            ]
        }
    },
    "required": ["data"],
    "definitions": {
        "integerData" : {
            "type": "integer",
            "minimum" : 0
        },
        "stringData" : {
            "type": "string"
        }
    }
}
JSON;

// Schema must be decoded before it can be used
$jsonSchemaObject = json_decode($jsonSchema);

// The SchemaStorage can resolve references, loading additional schemas from file as needed, etc.
$schemaStorage = new SchemaStorage();

// This does two things:
// 1) Mutates $jsonSchemaObject to normalize the references (now they will be to file://mySchema#/definitions/integerData, etc)
// 2) Tells $schemaStorage that references to file://mySchema... should be resolved by looking in $jsonSchemaObject
$schemaStorage->addSchema('file://mySchema', $jsonSchemaObject);

// Provide $schemaStorage to the Validator so that references can be resolved during validation
$jsonValidator = new Validator(Validator::CHECK_MODE_NORMAL, $schemaStorage);

// JSON must be decoded before it can be used
$jsonToValidateObject = json_decode('{"data":123}');

// Do validation (use isValid() and getErrors() to check the result)
$jsonValidator->check($jsonToValidateObject, $jsonSchemaObject);
```

## Running the tests

    $ vendor/bin/phpunit
